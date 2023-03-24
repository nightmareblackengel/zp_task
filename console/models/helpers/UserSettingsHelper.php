<?php
namespace console\models\helpers;

use console\models\mysql\UserSettingsModel;

/**
 * Испрользуется для вычисления настроек чата.
 * Вычисляются максимальные значения всех пользователей в чате по параметрам: "кол-во дней" или "кол-во сообщений"
 * Если у всех пользователей указанного чата - нет настроек, то возвращается "макс. кол-во вообщений" = ([5000, 0])
 */
class UserSettingsHelper
{
    const DEFAULT_VALUE = [5000, 0];

    public static function getSettValueFrom(array $setting): int
    {
        if (empty($setting['type']) || empty($setting['time'])) {
            return 0;
        }

        return UserSettingsModel::getTimeByValue($setting['time']);
    }

    /**
     * @return array|int[messageCount, daysCount]
     */
    public static function getParamsFromSetting($setting): array
    {
        $value = self::getSettValueFrom($setting);
        if (empty($value)) {
            return [0, 0];
        }
        if (UserSettingsModel::HISTORY_STORE_TYPE_DAY === $setting['type']) {
            return [0, $value];
        }

        return [$value, 0];
    }

    public static function prepareSettings(?array $settings): array
    {
        if (empty($settings)) {
            return self::DEFAULT_VALUE;
        }

        if (1 === count($settings)) {
            list($msgCount1, $daysCount1) = UserSettingsHelper::getParamsFromSetting($settings[0]);
            if (empty($msgCount1) && empty($daysCount1)) {
                return self::DEFAULT_VALUE;
            }

            return [$msgCount1, $daysCount1];
        } elseif (2 === count($settings)) {
            list($msgCount1, $daysCount1) = UserSettingsHelper::getParamsFromSetting($settings[0]);
            list($msgCount2, $daysCount2) = UserSettingsHelper::getParamsFromSetting($settings[1]);

            $maxMsgCount = max($msgCount1, $msgCount2);
            $maxDaysCount = max($daysCount1, $daysCount2);

            return [$maxMsgCount, $maxDaysCount];
        }

        list($msgCount1, $daysCount1) = UserSettingsHelper::getParamsFromSetting($settings[0]);
        list($msgCount2, $daysCount2) = UserSettingsHelper::getParamsFromSetting($settings[1]);
        list($msgCount3, $daysCount3) = UserSettingsHelper::getParamsFromSetting($settings[2]);

        $maxMsgCount = max($msgCount1, $msgCount2, $msgCount3);
        $maxDaysCount = max($daysCount1, $daysCount2, $daysCount3);

        return [$maxMsgCount, $maxDaysCount];
    }
}
