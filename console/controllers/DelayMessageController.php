<?php
namespace console\controllers;

use common\models\redis\DelayMsgSortedSetStorage;
use DateTime;
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
    // TODO:
    const MAX_CYCLE_TIME = 60;
//    const MAX_CYCLE_TIME = 5;

    public function actionIndex()
    {
        set_time_limit(120);

        $startTime = $this->getTimeStampWithStartAt0(0);
        $endTime = $startTime + self::MAX_CYCLE_TIME;

        $insertTime = $startTime;
        echo PHP_EOL, date('Y-m-d H:i:s', $insertTime), PHP_EOL;

        // до тех пор, пока скрипт не пройдет по всем секундам (от 0 до 59 включительно) от тек. минуты
        while ($insertTime < $endTime) {
            $currentTime = time();


            // вставка данных

            // если данных нет, то ожидаем


            echo PHP_EOL, 'ts=', date('Y-m-d H:i:s', $insertTime);
            echo PHP_EOL, 'cd=', date('Y-m-d H:i:s', $currentTime);
            // если "текущая секунда" больше "секунды вставки", то увеличиваем последнюю на 1
            if ($currentTime > $insertTime) {
                $insertTime++;
            } else {
                // ожидаем секунду
                usleep(1000000);
            }
        }

        $startTime = $endTime;
        $endTime = $endTime + 30;
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
    public function actionCreateTest($delayInSeconds = 60, $insertCount = 2000)
    {
        echo "action CreateTest started", PHP_EOL;

        $startAt = $this->getTimeStampWithStartAt0($delayInSeconds);
        $this->print_time();

        $resInserted = 0;
        for ($i = 0; $i < 60; $i++) {
            $this->print_time();

            for ($j = 0; $j < $insertCount; $j++) {
                $resInserted += (int) DelayMsgSortedSetStorage::getInstance()
                    ->addTo(
                        $startAt,
                        '[' . date('Y-m-d-H-i-s') . '] message-' . rand(1, 10000000)
                    );
            }
            $this->print_time();
        }

        echo PHP_EOL, 'inserted=[', $resInserted, ']', PHP_EOL;

        echo PHP_EOL, "action CreateTest ended", PHP_EOL;
        return false;
    }

    protected function print_time()
    {
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
