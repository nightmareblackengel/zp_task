<?php

namespace common\ext\redis;

abstract class RedisHashes extends RedisBase
{
    public function setValues($key, ...$values): int
    {
        return static::getStorage()->hset($this->prepareKey($key), ...$values);
    }

    public function getCertainField($key, $fieldName)
    {
        return static::getStorage()->hget($this->prepareKey($key), $fieldName);
    }

    public function getFewCertainFields($key, array $fields): array
    {
        return static::getStorage()->hmget($this->prepareKey($key), $fields);
    }

    public function getAllFields($key)
    {
        return static::getStorage()->hgetall($this->prepareKey($key));
    }

    public function getKeys($key)
    {
        return static::getStorage()->hkeys($this->prepareKey($key));
    }

    public function getValues($key)
    {
        return static::getStorage()->hvals($this->prepareKey($key));
    }
}
