<?php

namespace common\models\redis;

use common\ext\redis\RedisHashes;
use Yii;
use yii\redis\Connection;

class FlashHashesStorage extends RedisHashes
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb3;
    }

    public function prepareKey($key)
    {
        return "fl:$key";
    }
}
