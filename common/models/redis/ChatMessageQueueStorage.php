<?php

namespace common\models\redis;

use common\ext\redis\RedisQueues;
use Yii;
use yii\redis\Connection;

class ChatMessageQueueStorage extends RedisQueues
{
    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb3;
    }

    public function prepareKey(string $key)
    {
        return 'm:' . $key;
    }

    public function getList(string $key, int $start = 0, int $end = 10): array
    {
        $list = static::getStorage()->lrange($this->prepareKey($key), $start, $end);
        if (empty($list)) {
            return [];
        }

        foreach ($list as $key => $value) {
            $list[$key] = json_decode($value);
        }

        return $list;
    }
}
