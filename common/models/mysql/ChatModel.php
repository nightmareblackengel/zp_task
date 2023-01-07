<?php

namespace common\models\mysql;

use common\ext\base\MySqlModel;

class ChatModel extends MySqlModel
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    const IS_CHANNEL_FALSE = 0;
    const IS_CHANNEL_TRUE = 1;

    public static function tableName(): string
    {
        return '`chat`';
    }

    public static function getChatList(int $userId): ?array
    {
        $query = sprintf(
            "SELECT c.id as chatId, c.name, c.isChannel "
            . "FROM %s c "
            . "INNER JOIN %s uc ON uc.chatId = c.id "
            . "WHERE uc.userId = :userId "
            . "AND c.status = 1 "
            . "GROUP BY c.id",
            ChatModel::tableName(),
            UserChatModel::tableName()
        );

        return self::getDb()->createCommand($query, [':userId' => $userId])->queryAll();
    }
}
