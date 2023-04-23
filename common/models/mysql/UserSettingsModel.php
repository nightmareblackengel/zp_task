<?php

namespace common\models\mysql;

use common\ext\base\MySqlModel;

class UserSettingsModel extends MySqlModel
{
    const HISTORY_STORE_TYPE_DAY = 1;
    const HISTORY_STORE_TYPE_MESSAGE = 2;

    const HISTORY_STORE_MSG_1DAY = 1;
    const HISTORY_STORE_MSG_7DAY = 2;
    const HISTORY_STORE_MSG_30DAY = 3;

    const HISTORY_STORE_MSG_500MSG = 11;
    const HISTORY_STORE_MSG_1000MSG = 12;
    const HISTORY_STORE_MSG_5000MSG = 13;

    public static function tableName(): string
    {
        return '`user_setting`';
    }

    public static function getTimeByValue(int $value): int
    {
        if (self::HISTORY_STORE_MSG_1DAY === $value) {
            return 1;
        } elseif (self::HISTORY_STORE_MSG_7DAY === $value) {
            return 7;
        } elseif (self::HISTORY_STORE_MSG_30DAY === $value) {
            return 30;
        } elseif (self::HISTORY_STORE_MSG_500MSG === $value) {
            return 500;
        } elseif (self::HISTORY_STORE_MSG_1000MSG === $value) {
            return 1000;
        } elseif (self::HISTORY_STORE_MSG_5000MSG === $value) {
            return 5000;
        }

        return 0;
    }
}
