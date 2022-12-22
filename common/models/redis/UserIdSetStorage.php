<?php
namespace common\models\redis;

use common\ext\redis\RedisSets;
use Yii;
use yii\redis\Connection;

class UserIdSetStorage extends RedisSets
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb2;
    }

    public function prepareKey($key)
    {
        return "uid:$key";
    }
}
