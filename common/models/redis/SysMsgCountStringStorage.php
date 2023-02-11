<?php
namespace common\models\redis;

use common\ext\redis\RedisStrings;
use Yii;
use yii\redis\Connection;

class SysMsgCountStringStorage extends RedisStrings
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb4;
    }

    public function prepareKey($key)
    {
        return "cid:$key";
    }

    public function increment(int $chatId)
    {
        return static::getStorage()
            ->incr(static::prepareKey($chatId));
    }
}
