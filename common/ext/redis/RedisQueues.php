<?php

namespace common\ext\redis;

abstract class RedisQueues extends RedisBase
{
    public function addToHead(string $key, ...$values)
    {
        return static::getStorage()->lpush($this->prepareKey($key), ...$values);
    }

    public function addToTail(string $key, ...$values)
    {
        return static::getStorage()->rpush($this->prepareKey($key), ...$values);
    }

    public function getOffsetList(string $key, int $offset = 0, int $count = 10)
    {
        return static::getStorage()->lrange($this->prepareKey($key), $offset, $count);
    }

    public function getRemoveFromHead(string $key)
    {
        return static::getStorage()->lpop($this->prepareKey($key));
    }

    public function getRemoveFromTail(string $key)
    {
        return static::getStorage()->rpop($this->prepareKey($key));
    }
}
