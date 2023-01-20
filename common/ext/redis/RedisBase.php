<?php

namespace common\ext\redis;

use common\ext\patterns\Singleton;
use yii\base\BaseObject;
use yii\redis\Connection;

abstract class RedisBase extends BaseObject
{
    use Singleton;

    const TRANSACTION_QUEUED = 'QUEUED';

    abstract public static function getStorage(): Connection;

    public function prepareKey(string $key)
    {
        return $key;
    }

    public function isExists(string $key)
    {
        return static::getStorage()->exists($this->prepareKey($key));
    }

    public function ttl(string $key)
    {
        return static::getStorage()->ttl($this->prepareKey($key));
    }

    public function delete(string $key): int
    {
        return static::getStorage()->del($this->prepareKey($key));
    }

    public static function prepareArrayListToAssoc(array $arr)
    {
        $result = [];
        // TODO: test it
        for ($ind1 = 0; $ind1 < count($arr); $ind1) {
            $result[$arr[$ind1]] = $arr[$ind1 + 1];
            $ind1 += 2;
        }

        return $result;
    }
}
