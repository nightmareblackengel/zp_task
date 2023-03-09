<?php

namespace console\models\helpers;

use common\models\ChatMessageModel;
use Faker\Generator;

class MessageHelper
{
    public static function insertDelayMsgFrom(array &$list)
    {
        if (empty($list)) {
            return 0;
        }

        $insertCount = 0;
        foreach ($list as $item) {
            $messageItem = @json_decode($item['v'], true);
            if (empty($messageItem)) {
                continue;
            }
            if (empty($messageItem['c']) || empty($messageItem['u']) || empty($messageItem['m'])) {
                continue;
            }

            $insertCount += (int) ChatMessageModel::getInstance()
                ->insertMessage(
                    $messageItem['u'],
                    $messageItem['c'],
                    $messageItem['m'],
                    ChatMessageModel::MESSAGE_TYPE_SIMPLE,
                    $item['t']
                );
        }

        return $insertCount;
    }

    // генерирует указанное кол-во сообщений
    public static function generateNewMessages(Generator $faker, array $userIds, int $chatId, $needToInsertCount = 1000): int
    {
        $insertCount = 0;

        $timeStart = time() - 80*24*60*60;
        $timeEnd = time();

        $msgDateList = self::generateDates($needToInsertCount, $timeStart, $timeEnd);
        foreach ($msgDateList as $time) {
            $insertCount += (int) ChatMessageModel::getInstance()
                ->insertMessage(
                    $userIds[rand(0, count($userIds) - 1)],
                    $chatId,
                    '[' . date('Y-m-d_H-i-s', $time) . ']-' . rand(100000, 999999) . '-' . $faker->realText(),
                    ChatMessageModel::MESSAGE_TYPE_SIMPLE,
                    $time
                );
        }

        return $insertCount;
    }

    // генерация рандомных дат, в количестве $dateCount
    public static function generateDates(int $dateCount, int $timeStart, int $timeEnd)
    {
        $dates = [];
        for ($d = 0; $d < $dateCount; $d++) {
            $dates[$d] = rand($timeStart, $timeEnd);
        }
        sort($dates);

        return $dates;
    }
}
