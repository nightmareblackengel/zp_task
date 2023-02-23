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
    public function actionIndex()
    {
        return false;
    }

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
