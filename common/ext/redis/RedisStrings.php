<?php

namespace common\ext\redis;

use yii\base\BaseObject;
use yii\redis\Connection;

abstract class RedisStrings extends BaseObject
{
    abstract public static function getStorage(): Connection;

    public function prepareKey($key)
    {
        return $key;
    }

    public function getValue($key)
    {
        return $this->getStorage()->get($this->prepareKey($key));
    }

    public function setValue($key, $value, $options = [])
    {
        return $this->getStorage()->set($this->prepareKey($key), $value);
    }

    public function setExValue($key, $value, $timeout, $options = [])
    {
        return $this->getStorage()->setex($this->prepareKey($key), $timeout, $value);
    }

    public function removeKey($key)
    {
        return $this->getStorage()->del($this->prepareKey($key));
    }
}
