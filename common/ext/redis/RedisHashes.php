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

    public function getFewCertainFields($key, ...$values): array
    {
        return static::getStorage()->hmget($this->prepareKey($key), ...$values);
    }

    public function getAllFields($key)
    {
        $result = static::getStorage()->hgetall($this->prepareKey($key));

        return static::prepareArrayListToAssoc($result);
    }

    public function getKeys($key)
    {
        return static::getStorage()->hkeys($this->prepareKey($key));
    }

    public function getValues($key)
    {
        return static::getStorage()->hvals($this->prepareKey($key));
    }

    public function deleteCertainField($key, ...$fields)
    {
        return static::getStorage()->hdel($this->prepareKey($key), ...$fields);
    }
}
