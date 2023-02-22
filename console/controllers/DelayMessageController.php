<?php
namespace console\controllers;

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

    public function actionCreateTest()
    {
        return false;
    }
}
