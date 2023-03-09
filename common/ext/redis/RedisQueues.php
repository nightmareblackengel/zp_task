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

    public function getQueueLength(string $key)
    {
        return static::getStorage()->llen($this->prepareKey($key));
    }

    public function getRemoveOneFromHead(string $key)
    {
        return static::getStorage()->lpop($this->prepareKey($key));
    }

    public function getRemoveOneFromTail(string $key)
    {
        return static::getStorage()->rpop($this->prepareKey($key));
    }

    /**
     * @param string $key
     * @param int $start - позиция, до которой все элементы будут удалены
     * @param int $end - позиция, после которой все элементы будут удалены (-1 = эта часть удаления не будет выполняться)
     * @return mixed
     */
    public function removeItemCount(string $key, int $start, int $end = -1)
    {
        return static::getStorage()->ltrim($this->prepareKey($key), $start, $end);
    }
}
