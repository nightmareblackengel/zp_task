<?php

namespace common\models\mysql;

use common\ext\base\MySqlModel;

class UserChatModel extends MySqlModel
{
    const IS_USER_BANNED_NO = 0;
    const IS_USER_BANNED_YES = 1;

    const IS_CHAT_OWNER_NO = 0;
    const IS_CHAT_OWNER_YES = 1;

    public static function tableName(): string
    {
        return '`user_chat`';
    }

    public function isUserBelongToChat(int $userId, int $chatId): bool
    {
        $queryStr = sprintf(
            "SELECT userId, chatId, isUserBanned FROM %s WHERE userId = :userId AND chatId = :chatId",
            static::tableName()
        );
        $item = static::getDb()
            ->createCommand($queryStr, [
                ':userId' => $userId,
                ':chatId' => $chatId,
            ])->queryOne();

        if (empty($item)) {
            return false;
        }
        if (!empty($item['isUserBanned']) && $item['isUserBanned'] === self::IS_USER_BANNED_YES) {
            return false;
        }

        return true;
    }
}
