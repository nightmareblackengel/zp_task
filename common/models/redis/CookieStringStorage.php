<?php

namespace common\models\redis;

use common\ext\redis\RedisStrings;
use Yii;
use yii\redis\Connection;

class CookieStringStorage extends RedisStrings
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb2;
    }

    public function prepareKey($key)
    {
        return "c2:$key";
    }
}
