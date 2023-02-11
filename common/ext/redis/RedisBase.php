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

    public static function prepareArrayListToAssoc(array $arr): array
    {
        $result = [];
        for ($ind1 = 0; $ind1 < count($arr); $ind1 += 2) {
            $result[$arr[$ind1]] = $arr[$ind1 + 1];
        }

        return $result;
    }

    public static function prepareArrListToAssocWithKeys(array $arr, $delimiter = ':'): array
    {
        $result = [];
        for ($ind1 = 0; $ind1 < count($arr); $ind1 += 2) {
            $keyParts = explode($delimiter, $arr[$ind1]);
            $newIndex = $keyParts[count($keyParts) - 1];
            $result[$newIndex] = $arr[$ind1 + 1];
        }

        return $result;
    }
}
