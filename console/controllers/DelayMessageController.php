<?php
namespace console\controllers;

use common\ext\console\ConsoleController;
use common\models\ChatMessageModel;
use common\models\redis\CronDelayMsgRunner;
use common\models\redis\DelayMsgSortedSetStorage;
use DateTime;
use Faker\Factory;

/**
 * index:
        каждую минуту по крону запускается этот экшн
        с 1й по 59, каждую секунду (до 60й) в этом цикле
            - получаем данные список "отложенных сообщений на эту секунду" (из SortOrder)
            - сохраняем каждое сообщение в чат (Lists)
            - удаляем сообщения (из SortOrder)
            - переход к следующей секунде

        с 60й по 90 секунду (если есть данные в SortOrder), то
            выполняются те же действия выше, только выбираются все данные с 0 до "текущей секунды"
 */
class DelayMessageController extends ConsoleController
{
    const MAX_CYCLE_TIME = 60;

    const TEST_USER_ID = 5;
    const TEST_CHAT_ID = 27;

    public function actionIndex($showLog = 0)
    {
        $showLog = (int) $showLog;

        $this->setMaxTimeAndMemory(120, '1024M');
        if (!$this->checkIfCorrectDockerRun()) {
            return '';
        }

        $startTime = $this->getTimeStampWithStartAt0(0);
        $endTime = $startTime + self::MAX_CYCLE_TIME;
        $insertTime = $startTime;
        echo PHP_EOL, 'Started at=', date('Y-m-d H:i:s', $insertTime), PHP_EOL;

        $canRun = CronDelayMsgRunner::getInstance()->canRunCronAction($startTime);
        if (!$canRun) {
            echo PHP_EOL, "Can't run second script on certain time", PHP_EOL;
            return '';
        }

        $insertTotal = 0;
        // до тех пор, пока скрипт не пройдет по всем секундам (от 0 до 59 включительно) от тек. минуты
        while ($insertTime < $endTime) {
            $currentTime = time();
            $insertTotal += $this->getFromSoAndInsertIntoList($insertTime, $insertTime, $showLog);
            // если "текущая секунда" больше "секунды вставки", то увеличиваем последнюю на 1
            if ($currentTime <= $insertTime) {
                // ожидаем секунду
                usleep(1000000);
            }
            $insertTime++;
        }

        $insertedCount = $this->getFromSoAndInsertIntoList(0, $endTime, false);
//        if ($showLog) {
            echo 'inserted message count =[', $insertTotal, '] Inserted late message count =[', $insertedCount, ']', PHP_EOL;
//        }

        return '';
    }

    // создание тестовых записей, с "отрывом в секундах",
    // если значение 60, то будет вставка с timestamp "следующей минуты"
    public function actionCreateTest($delayInSeconds = 60, $insertCount = 2000, $showLog = 1)
    {
        echo "action CreateTest started", PHP_EOL;

        $showLog = (int) $showLog;
        $delayInSeconds = (int) $delayInSeconds;
        $insertCount = (int) $insertCount;
        if (empty($insertCount)) {
            echo PHP_EOL, "Error. Incorrect params. Script exit.", PHP_EOL;
            return '';
        }
        set_time_limit(120);
        ini_set('memory_limit', '1024M');

        $startAt = $this->getTimeStampWithStartAt0($delayInSeconds);
        $this->print_time($showLog);

        $resInserted = 0;
        $faker = Factory::create('ru_RU');
        for ($i = 0; $i < 60; $i++) {
            $this->print_time($showLog);
            for ($j = 0; $j < $insertCount; $j++) {
                $resInserted += (int) DelayMsgSortedSetStorage::getInstance()
                    ->addTo(
                        $startAt + $i, [
                            'c' => self::TEST_CHAT_ID,
                            'u' => self::TEST_USER_ID,
                            'm' => '[' . date('Y-m-d_H-i-s') . '] message-' . rand(100000, 999999). ($j + $i*$j) . '-' . $faker->realText(),
                        ]
                );
            }
            $this->print_time($showLog);
        }
        echo PHP_EOL, 'action CreateTest ended;', PHP_EOL, 'inserted=[', $resInserted, ']', PHP_EOL;

        return false;
    }

    // реализовать вставку удаление на тестовых данных, а затем переходить на "реальное содержимое"
    protected function getFromSoAndInsertIntoList(int $timeStart, $timeEnd, int $showLog): ?int
    {
        $this->print_time($showLog);
        $insertList = DelayMsgSortedSetStorage::getInstance()
            ->getData($timeStart, $timeEnd, true, true);
        if (empty($insertList)) {
            return 0;
        }

        $insertCount = $this->insertMessages($insertList, $showLog);
        $delCount = (int) DelayMsgSortedSetStorage::getInstance()->removeByScore($timeStart, $timeEnd);
//        if ($showLog) {
//            echo PHP_EOL, 'deleted items=[', $delCount, ']', PHP_EOL;
//        }

        return $insertCount;
    }

    protected function insertMessages(array &$list, $showLog = true): int
    {
        if (empty($list)) {
            return 0;
        }

        $insertCount = 0;
        foreach ($list as $item) {
            $messageItem = @json_decode($item['v'], true);
            if (empty($messageItem)) {
                continue;
            }
            if (empty($messageItem['c']) || empty($messageItem['u']) || empty($messageItem['m'])) {
                continue;
            }

            $insertCount += (int) ChatMessageModel::getInstance()
                ->insertMessage(
                    $messageItem['u'],
                    $messageItem['c'],
                    $messageItem['m'],
                    ChatMessageModel::MESSAGE_TYPE_SIMPLE,
                    $item['t']
                );
        }
//        if ($showLog) {
//            $this->print_time($showLog);
//            echo PHP_EOL, 'inserted items=[', $insertCount, ']', PHP_EOL;
//        }

        return $insertCount;
    }

    protected function print_time(int $showLog = 1)
    {
//        if (empty($showLog)) {
            return;
//        }
        $now = DateTime::createFromFormat('U.u', microtime(true));
        echo PHP_EOL, $now->format("m-d-Y H:i:s.u");
    }

    protected function getTimeStampWithStartAt0($delayInSeconds)
    {
        $startAt = time() + (int) $delayInSeconds;

        return $startAt - ($startAt % 60);
    }

    // удаляет только лишь указанные в score значения
//    protected function removeCertain(int $certainScore)
//    {
//        echo PHP_EOL, 'remove score = ', $certainScore, PHP_EOL;
//        $res = DelayMsgSortedSetStorage::getInstance()->getData($certainScore, $certainScore, true, true);
//        print_r2($res, 'Elements for remove');
//        $resR = DelayMsgSortedSetStorage::getInstance()->removeByScore($certainScore, $certainScore);
//        var_dump($resR); echo PHP_EOL;
//        $res = DelayMsgSortedSetStorage::getInstance()->getData(0, 100000000000, true);
//        print_r2($res, 'least elements');
//    }
}
