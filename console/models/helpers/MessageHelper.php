<?php

namespace console\models\helpers;

use common\models\ChatMessageModel;

class MessageHelper
{
    public static function insertMsgFrom(array &$list)
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
}
