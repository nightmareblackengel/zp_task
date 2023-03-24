<?php

namespace console\models\redis;

use common\ext\redis\RedisStrings;
use Yii;
use yii\redis\Connection;

class CronCheckerForMsgRunStorage extends RedisStrings
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb5;
    }

    public function prepareKey($key)
    {
        return "cron-rm-one-process";
    }

    public function canRunCronAction($ttl = 60*60): bool
    {
        $key = $this->prepareKey(null);

        $value = static::getStorage()->get($key);
        if (!empty($value)) {
            return false;
        }
        $saveRes = (int) static::getStorage()->set($key, '1');
        static::getStorage()->expire($key, $ttl);

        return $saveRes === 1;
    }

    public function removeKey()
    {
        return (int) static::getStorage()->del($this->prepareKey(null));
    }
}
