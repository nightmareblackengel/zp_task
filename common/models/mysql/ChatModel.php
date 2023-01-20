<?php

namespace common\models\mysql;

use common\ext\base\MySqlModel;
use common\models\ChatMessageModel;

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

    public static function prepareChatListWithCount(int $userId): array
    {
        // get storage data
        $chatListRes = [];
        $baseChatList = ChatModel::getChatList($userId);
        if (empty($baseChatList)) {
            return [];
        }
        // prepare result with 'index key'
        $chatIds = [];
        foreach ($baseChatList as $chatItem) {
            $chatIds[] = $chatItem['chatId'];
            $chatListRes[$chatItem['chatId']] = $chatItem;
        }
        unset($baseChatList);
        // get count for all chats
        $chatCountList = ChatMessageModel::getInstance()->getChatListMsgCount($chatIds);
        if (empty($chatCountList)) {
            return $chatListRes;
        }
        // add "count" to result
        foreach ($chatCountList as $chatId => $msgCount) {
            $chatListRes[$chatId]['count'] = $msgCount;
        }

        return $chatListRes;
    }
}
