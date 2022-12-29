<?php

namespace common\ext\redis;

use yii\base\BaseObject;
use yii\redis\Connection;

abstract class RedisBase extends BaseObject
{
    abstract public static function getStorage(): Connection;

    public function prepareKey($key)
    {
        return $key;
    }

    public function isExists($key)
    {
        return static::getStorage()->exists($this->prepareKey($key));
    }

    public function ttl($key)
    {
        return static::getStorage()->ttl($this->prepareKey($key));
    }

    public function delete($key): int
    {
        return static::getStorage()->del($this->prepareKey($key));
    }
}
