<?php
namespace common\models\redis;

use common\ext\redis\RedisHashes;
use Yii;
use yii\redis\Connection;

class SysMsgCountHashStorage extends RedisHashes
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb4;
    }

    public function prepareKey($key)
    {
        return "uid:$key";
    }

    public function increment(int $userId, int $chatId)
    {
        return static::getStorage()
            ->hincrby(static::prepareKey($userId), "c:$chatId", 1);
    }
}
