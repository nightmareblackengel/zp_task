<?php
namespace common\models\redis;

use common\ext\redis\RedisStrings;
use yii;
use yii\redis\Connection;

class CronDelayMsgRunner extends RedisStrings
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb5;
    }

    public function prepareKey($key)
    {
        return "cron-dm-" . $key;
    }

    public function canRunCronAction($time, $ttl = 2*60)
    {
        $key = $this->prepareKey($time);

        $value = (int) static::getStorage()->incr($key);
        static::getStorage()->expire($key, $ttl);

        return $value === 1;
    }
}
