<?php

namespace common\models\redis;

use common\ext\redis\RedisHashes;
use Yii;
use yii\redis\Connection;

class ChatDateTimeMhashStorage extends RedisHashes
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb3;
    }

    public function prepareKey(string $key = null)
    {
        return 'chat-date-time-full';
    }
}
