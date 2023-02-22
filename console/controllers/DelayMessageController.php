<?php
namespace console\controllers;

use common\models\redis\DelayMsgSortedSetStorage;
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

    public function actionCreateTest($delayInSeconds = 60)
    {
        echo "action CreateTest started", PHP_EOL;
        $startAt = time();

        for ($i = 0; $i < 10; $i++) {
            DelayMsgSortedSetStorage::getInstance()->addTo(
                $startAt,
                '[' . date('Y-m-d-H-i-s') . '] message-' . rand(1, 10000));
        }

//        $res = DelayMsgSortedSetStorage::getInstance()->getData(0, 100000000000, true);
//        print_r2($res);
        $res = DelayMsgSortedSetStorage::getInstance()->getData(1677103277, 1677103277, true, true);
        print_r2($res);

        $resR = DelayMsgSortedSetStorage::getInstance()->removeByScore(1677103277, 1677103277);
        var_dump($resR); echo PHP_EOL;

        $res = DelayMsgSortedSetStorage::getInstance()->getData(0, 100000000000, true);
        print_r2($res);


        echo PHP_EOL, "action CreateTest ended", PHP_EOL;
        return false;
    }
}
