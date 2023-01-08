<?php

namespace common\ext\redis;

abstract class RedisQueues extends RedisBase
{
    public function addToHead(string $key, ...$values)
    {
        return static::getStorage()->lpush($key, ...$values);
    }

    public function addToTail(string $key, ...$values)
    {
        return static::getStorage()->rpush($key, ...$values);
    }

    public function getOffsetList(string $key, int $offset = 0, int $count = 10)
    {
        return static::getStorage()->lrange($key, $offset, $count);
    }

    public function getRemoveFromHead(string $key)
    {
        return static::getStorage()->lpop($key);
    }

    public function getRemoveFromTail(string $key)
    {
        return static::getStorage()->rpop($key);
    }
}
