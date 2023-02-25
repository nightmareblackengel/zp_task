<?php
namespace console\controllers;

use common\models\ChatMessageModel;
use common\models\redis\DelayMsgSortedSetStorage;
use DateTime;
use Faker\Factory;
use yii\console\Controller;

/**
 * index:
        каждую минуту по крону запускается этот экшн
        с 1й по 59 секунду (до 60й) в этом цикле

            1. выполняется ZRANGE [название] [текущая секунда минуты] [текущая секунда минуты] BYSCORE WITHSCORES
                (пример  ZRANGE x 100 100 BYSCORE WITHSCORES )
                получаем результат
                если пусто - ожидание следующей секунды
                иначе - обработка данных - получение данных и сохранение их как сообщений
            2. выполнение ZREMRANGEBYSCORE [название] [текущая секунда минуты] [текущая секунда минуты]
                которая удаляет все данные сообщений за "эту" секунду
            3. переход к следующей секунде
        с 60й по 90 секунду (если есть данные) выполняется
            метод "получения/удаления" записей из "старых или необработанных данных"
            ZRANGE [название] 0 [последняя секунда минуты] BYSCORE WITHSCORES LIMIT 0 100
 */
class DelayMessageController extends Controller
{
    const MAX_CYCLE_TIME = 60;

    const TEST_USER_ID = 5;
    const TEST_CHAT_ID = 27;

    // TODO:
    // ограничить запуск скрипта до 2х в минуту
    // до одного в минуту
    public function actionIndex($showLog = 1)
    {
        set_time_limit(120);
        ini_set('memory_limit', '1024M');

        $showLog = (int) $showLog;

        $startTime = $this->getTimeStampWithStartAt0(0);
        $endTime = $startTime + self::MAX_CYCLE_TIME;

        $insertTime = $startTime;
        echo PHP_EOL, date('Y-m-d H:i:s', $insertTime), PHP_EOL;

        // до тех пор, пока скрипт не пройдет по всем секундам (от 0 до 59 включительно) от тек. минуты
        while ($insertTime < $endTime) {
            $currentTime = time();
            $insertedCount = $this->getFromSoAndInsertIntoList($insertTime, $showLog);
            // если "текущая секунда" больше "секунды вставки", то увеличиваем последнюю на 1
            if ($currentTime > $insertTime) {
                $insertTime++;
            } else {
                // ожидаем секунду
                usleep(1000000);
            }
        }

        echo PHP_EOL, "LAST CYCLE", PHP_EOL;
        exit();

        $dataExists = true;
        while ($dataExists) {
            // читаем данные

            // если данных нет - выходим
            $dataExists = false;
        }

        return false;
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
        set_time_limit(10);
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
    protected function getFromSoAndInsertIntoList(int $time, int $showLog): ?int
    {
        $this->print_time($showLog);
        $insertList = DelayMsgSortedSetStorage::getInstance()
            ->getData($time, $time, true, false);
        if (empty($insertList)) {
            return 0;
        }

        $insertCount = $this->insertMessages($time, $insertList, $showLog);
        $delCount = (int) DelayMsgSortedSetStorage::getInstance()->removeByScore($time, $time);
        if ($showLog) {
            echo PHP_EOL, 'deleted items=[', $delCount, ']', PHP_EOL;
        }

        return $insertCount;
    }

    protected function insertMessages($time, array &$list, $showLog = true): int
    {
        if (empty($list)) {
            return 0;
        }

        $insertCount = 0;
        foreach ($list as $item) {
            $messageItem = @json_decode($item, true);
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
                    $time
                );
        }
        if ($showLog) {
            $this->print_time($showLog);
            echo PHP_EOL, 'inserted items=[', $insertCount, ']', PHP_EOL;
        }

        return $insertCount;
    }

    protected function print_time(int $showLog = 1)
    {
        if (empty($showLog)) {
            return;
        }
        $now = DateTime::createFromFormat('U.u', microtime(true));
        echo PHP_EOL, $now->format("m-d-Y H:i:s.u");
    }

    protected function getTimeStampWithStartAt0($delayInSeconds)
    {
        $startAt = time() + (int) $delayInSeconds;

        return $startAt - ($startAt % 60);
    }

    // удаляет только лишь указанные в score значения
    protected function removeCertain(int $certainScore)
    {
        echo PHP_EOL, 'remove score = ', $certainScore, PHP_EOL;
        $res = DelayMsgSortedSetStorage::getInstance()->getData($certainScore, $certainScore, true, true);
        print_r2($res, 'Elements for remove');
        $resR = DelayMsgSortedSetStorage::getInstance()->removeByScore($certainScore, $certainScore);
        var_dump($resR); echo PHP_EOL;
        $res = DelayMsgSortedSetStorage::getInstance()->getData(0, 100000000000, true);
        print_r2($res, 'least elements');
    }
}
