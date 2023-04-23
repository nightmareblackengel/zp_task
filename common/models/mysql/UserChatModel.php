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

    public function saveUserChat($userId, $chatId, $isOwner)
    {
        $userChatParams = [
            'userId' => $userId,
            'chatId' => $chatId,
        ];
        if (!empty($isOwner)) {
            $userChatParams['isChatOwner'] = self::IS_CHAT_OWNER_YES;
        }

        return self::getInstance()
            ->insertBy($userChatParams);
    }
}
