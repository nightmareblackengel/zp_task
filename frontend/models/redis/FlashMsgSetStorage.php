<?php

namespace frontend\models\redis;

use common\ext\redis\RedisStrings;
use Yii;
use yii\redis\Connection;

class FlashMsgSetStorage extends RedisStrings
{
    protected $keyPrefix = 'chat_msg:';
    protected $defaultTtl = 60*60;

    public function prepareKey(string $key)
    {
        return $this->keyPrefix . $key;
    }

    public static function getStorage(): Connection
    {
        return Yii::$app->redisDb5;
    }

    public function setExValue(string $key, $value, $timeout = null, $options = [])
    {
        return parent::setExValue($key, $value, $this->defaultTtl, $options);
    }
}
