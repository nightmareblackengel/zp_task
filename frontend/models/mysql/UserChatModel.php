<?php
namespace frontend\models\mysql;

use common\models\mysql\ChatModel;

class UserChatModel extends \common\models\mysql\UserChatModel
{
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
        // TODO: static cache
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
