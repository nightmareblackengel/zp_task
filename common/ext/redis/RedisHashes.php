<?php

namespace common\ext\redis;

abstract class RedisHashes extends RedisBase
{
    public function setValue(?string $key, int $propertyKey, $propertyValue): int
    {
        return static::getStorage()->hmset(static::prepareKey($key), $propertyKey, $propertyValue);
    }

    public function setValues(?string $key, ...$values)
    {
        return static::getStorage()->hset($this->prepareKey($key), ...$values);
    }

    public function getCertainField(?string $key, $fieldName)
    {
        return static::getStorage()->hget($this->prepareKey($key), $fieldName);
    }

    public function getFewCertainFields(?string $key, ...$values): array
    {
        return static::getStorage()->hmget($this->prepareKey($key), ...$values);
    }

    public function getAllFields(?string $key): array
    {
        $result = static::getStorage()->hgetall($this->prepareKey($key));
        if (empty($result)) {
            return [];
        }

        return static::prepareArrListToAssocWithKeys($result);
    }

    public function getKeys(?string $key)
    {
        return static::getStorage()->hkeys($this->prepareKey($key));
    }

    public function getValues(?string $key)
    {
        return static::getStorage()->hvals($this->prepareKey($key));
    }

    public function deleteCertainField(?string $key, ...$fields)
    {
        return static::getStorage()->hdel($this->prepareKey($key), ...$fields);
    }
}
