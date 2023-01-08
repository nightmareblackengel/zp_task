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

    public function getOffsetList(string $key, int $offset = 0, int $count = 10): array
    {
        $list = static::getStorage()->lrange($key, $offset, $count);
        if (empty($list)) {
            return [];
        }

        foreach ($list as $key => $value) {
            $list[$key] = json_decode($value);
        }

        return $list;
    }
}
