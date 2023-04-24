<?php
namespace frontend\models\mysql;

use common\models\mysql\ChatModel;

class UserChatModel extends \common\models\mysql\UserChatModel
{
    protected static $userOwnerList = [];

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
        $key = $userId . ':' .$chatId;
        if (in_array($key, self::$userOwnerList)) {
            return self::$userOwnerList[$key];
        }
        $item = static::getItemBy([
            'userId' => $userId,
            'chatId' => $chatId,
        ], '`userId`, `chatId`, `isChatOwner`');
        if (empty($item) || empty($item['isChatOwner']) || $item['isChatOwner'] === self::IS_CHAT_OWNER_NO) {
            self::$userOwnerList[$key] = false;
        } else {
            self::$userOwnerList[$key] = true;
        }

        return self::$userOwnerList[$key];
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
