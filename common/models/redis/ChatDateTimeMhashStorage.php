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

    public function getChatLastDtList(array $chatIds): array
    {
        if (empty($chatIds)) {
            return [];
        }

        $dates = $this->getFewCertainFields(null, ...$chatIds);
        if (empty($dates) || count($dates) != count($chatIds)) {
            return [];
        }

        $result = [];
        for ($i = 0; $i < count($chatIds); $i++) {
            $result[$chatIds[$i]] = (int) $dates[$i];
        }

        return $result;
    }
}
