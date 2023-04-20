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

    public function prepareKey(string $key)
    {
        return 'chat-date-time:' . $key;
    }

    public function setChatDateTime(int $userId, int $chatId, $time)
    {
        return static::getStorage()->hmset(static::prepareKey($userId), $chatId, $time);
    }
}
