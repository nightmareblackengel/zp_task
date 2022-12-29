<?php

namespace common\ext\redis;

abstract class RedisStrings extends RedisBase
{
    public function getValue($key)
    {
        return static::getStorage()->get($this->prepareKey($key));
    }

    public function setValue($key, $value, $options = [])
    {
        return static::getStorage()->set($this->prepareKey($key), $value);
    }

    public function setExValue($key, $value, $timeout, $options = [])
    {
        return static::getStorage()->setex($this->prepareKey($key), $timeout, $value);
    }
}
