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

    public function isUserEmailBelongToChat(int $chatId, ?string $userEmail): ?bool
    {
        $queryStr = sprintf(
            "SELECT `userId`, `chatId`, `isUserBanned` "
                . "FROM %s uc "
                . "INNER JOIN %s u ON u.id = uc.userId "
                . "WHERE uc.`chatId` = :chatId "
                . "AND u.`email` = :userEmail ",
            UserChatModel::tableName(),
            UserModel::tableName()
        );

        $item = static::getDb()
            ->createCommand($queryStr, [
                ':userEmail' => $userEmail,
                ':chatId' => $chatId,
            ])->queryOne();

        if (empty($item)) {
            return false;
        }
        if (!empty($item['isUserBanned'])) {
            return null;
        }

        return true;
    }

    public function saveUserChat($userId, $chatId, $isOwner)
    {
        $userChatParams = [
            'userId' => $userId,
            'chatId' => $chatId,
        ];
        if (!empty($isOwner)) {
            $userChatParams['isChatOwner'] = UserChatModel::IS_CHAT_OWNER_YES;
        }

        return UserChatModel::getInstance()
            ->insertBy($userChatParams);
    }

    public function getUserPrivateChatIds(int $firstUser, int $secondUser): array
    {
        $query = sprintf("SELECT `chatId`
            FROM %s
            WHERE `chatId` IN (
                SELECT uc.`chatId`
                FROM %s uc
                INNER JOIN %s c ON c.`id` = uc.`chatId`
                WHERE uc.`userId` = :firstUser
                AND c.`isChannel`=0
            )
            AND `userId` = :secondUser",
            static::tableName(),
            static::tableName(),
            ChatModel::tableName()
        );

        $chatIds = self::getDb()
            ->createCommand($query, [
                ':firstUser' => $firstUser,
                ':secondUser' => $secondUser
            ])->queryAll();
        if (empty($chatIds)) {
            return [];
        }

        return array_column($chatIds, 'chatId');
    }

    public function isUserChatOwner(int $userId, int $chatId): bool
    {
        $item = static::getItemBy([
            'userId' => $userId,
            'chatId' => $chatId,
        ], '`userId`, `chatId`, `isChatOwner`');
        if (empty($item)) {
            return false;
        }
        if (empty($item['isChatOwner']) || $item['isChatOwner'] === self::IS_CHAT_OWNER_NO) {
            return false;
        }

        return true;
    }

    public function getChatOwnerId(int $chatId): ?int
    {
        $item = static::getItemBy([
            'chatId' => $chatId,
            'isChatOwner' => self::IS_CHAT_OWNER_YES,
        ], '`userId`, `chatId`');
        if (empty($item['userId'])) {
            return null;
        }

        return $item['userId'];
    }
}
