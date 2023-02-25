<?php

namespace common\ext\redis;

abstract class RedisSortedSets extends RedisBase
{
    public function addTo(int $num, array $uniqueData): ?int
    {
        return (int) static::getStorage()
            ->zadd(
                $this->prepareKey(),
                $num,
                json_encode($uniqueData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );
    }

    public function getData(int $rangeFrom, int $rangeTill, bool $useByScore = false, bool $useWithScores = false, int $limit = 0): ?array
    {
        $cmdParams = [$this->prepareKey(), $rangeFrom, $rangeTill];
        if ($useByScore) {
            $cmdParams[] = "BYSCORE";
        }
        if ($useWithScores) {
            $cmdParams[] = "WITHSCORES";
        }
        if ($limit) {
            $cmdParams[] = sprintf("LIMIT 0 %d", $limit);
        }

        $data = static::getStorage()->executeCommand('ZRANGE', $cmdParams);
        if (empty($data)) {
            return null;
        }
        if (!$useWithScores) {
            return $data;
        }

        $parsedRes = [];
        for ($j = 0; $j < count($data); $j+=2) {
            $parsedRes[] = [
                'v' => $data[$j],
                't' => $data[$j + 1],
            ];
        }
        unset($data);

        return $parsedRes;
    }

    public function removeByScore(int $rangeFrom, int $rangeTill): ?int
    {
        return (int) static::getStorage()->zremrangebyscore($this->prepareKey(), $rangeFrom, $rangeTill);
    }
}
