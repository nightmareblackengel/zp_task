<?php

namespace common\ext\redis;

abstract class RedisStrings extends RedisBase
{
    public function getValue(string $key)
    {
        return static::getStorage()->get($this->prepareKey($key));
    }

    public function setValue(string $key, $value, $options = [])
    {
        return static::getStorage()->set($this->prepareKey($key), $value);
    }

    public function setExValue(string $key, $value, $timeout, $options = [])
    {
        return static::getStorage()->setex($this->prepareKey($key), $timeout, $value);
    }
}
