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

    public static function getHistoryStoreTypeList(): array
    {
        return [
            self::HISTORY_STORE_TYPE_DAY => 'По кол-ву дней',
            self::HISTORY_STORE_TYPE_MESSAGE => 'По кол-ву сообщений',
        ];
    }

    public static function getStoreTypeDropdownOptions(): array
    {
        return [
            self::HISTORY_STORE_TYPE_DAY => [
                'class' => 'nbeStoreType',
            ],
            self::HISTORY_STORE_TYPE_MESSAGE => [
                'class' => 'nbeStoreType',
            ]
        ];
    }

    public static function getHistoryStoreTime(?int $historyType = 0): array
    {
        $result = [];
        if (empty($historyType) || $historyType === self::HISTORY_STORE_TYPE_DAY) {
            $result[self::HISTORY_STORE_MSG_1DAY] = '1 день';
            $result[self::HISTORY_STORE_MSG_7DAY] = '7 дней';
            $result[self::HISTORY_STORE_MSG_30DAY] = '30 дней';
        }

        if (empty($historyType) || $historyType === self::HISTORY_STORE_TYPE_MESSAGE) {
            $result[self::HISTORY_STORE_MSG_500MSG] = '500 сообщений';
            $result[self::HISTORY_STORE_MSG_1000MSG] = '1000 сообщений';
            $result[self::HISTORY_STORE_MSG_5000MSG] = '5000 сообщений';
        }

        return $result;
    }

    public static function getStoreTimeDropdownOptions(): array
    {
        return [
            self::HISTORY_STORE_MSG_1DAY => [
                'class' => 'nbeStoreTime',
                'data' => [
                    'type' => self::HISTORY_STORE_TYPE_DAY,
                ],
            ],
            self::HISTORY_STORE_MSG_7DAY => [
                'class' => 'nbeStoreTime',
                'data' => [
                    'type' => self::HISTORY_STORE_TYPE_DAY,
                ],
            ],
            self::HISTORY_STORE_MSG_30DAY => [
                'class' => 'nbeStoreTime',
                'data' => [
                    'type' => self::HISTORY_STORE_TYPE_DAY,
                ],
            ],
            self::HISTORY_STORE_MSG_500MSG => [
                'class' => 'nbeStoreTime',
                'data' => [
                    'type' => self::HISTORY_STORE_TYPE_MESSAGE,
                ],
            ],
            self::HISTORY_STORE_MSG_1000MSG => [
                'class' => 'nbeStoreTime',
                'data' => [
                    'type' => self::HISTORY_STORE_TYPE_MESSAGE,
                ],
            ],
            self::HISTORY_STORE_MSG_5000MSG => [
                'class' => 'nbeStoreTime',
                'data' => [
                    'type' => self::HISTORY_STORE_TYPE_MESSAGE,
                ],
            ],
        ];
    }
}
