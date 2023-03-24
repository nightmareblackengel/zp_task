<?php
namespace console\models\mysql;

use common\models\mysql\UserChatModel;

class UserSettingsModel extends \common\models\mysql\UserSettingsModel
{
    /**
     * Варианты возвращаемых значений
     * пусто - невозвможно
     * одна строка - все типы настроек одинакового типа (одного из двух типов, либо нулл)
     * две строки - типы настроек двух типов (либо одного и нулевые)
     * три строки - есть настройки с нулевыми значениями
     */
    public function getChatUserSettings(int $chatId): array
    {
        $query = sprintf("
                SELECT MAX(uc.`chatId`) AS chatId, MAX(us.`historyStoreType`) AS 'type', MAX(us.`historyStoreTime`) AS 'time'
                FROM %s uc
                LEFT JOIN %s us ON us.`userId` = uc.`userId`
                WHERE uc.`chatId` = '%d'
                GROUP BY uc.`chatId`, us.`historyStoreType`
            ",
            UserChatModel::tableName(),
            static::tableName(),
            $chatId
        );

        $list = static::getDb()->createCommand($query)->queryAll();
        if (empty($list)) {
            return [];
        }

        return $list;
    }
}
