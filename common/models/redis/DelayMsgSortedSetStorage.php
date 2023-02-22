<?php
namespace common\models\redis;

use common\ext\redis\RedisSortedSets;
use yii;
use yii\redis\Connection;

class DelayMsgSortedSetStorage extends RedisSortedSets
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb5;
    }

    public function prepareKey($key = null)
    {
        return "delay-msg";
    }
}
