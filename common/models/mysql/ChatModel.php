<?php

namespace common\models\mysql;

use common\ext\base\MySqlModel;
use common\models\ChatMessageModel;
use Exception;

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
            "SELECT c.id as chatId, "
            . "CASE WHEN c.isChannel=1 THEN c.name "
            . "ELSE vw.email END as name, "
            . "c.isChannel "
            . "FROM %s c "
            . "INNER JOIN %s uc ON uc.chatId = c.id "
            . "LEFT JOIN vw_chat_user_name vw ON vw.user1 = uc.userId AND uc.chatId = vw.chatId "
            . "WHERE uc.userId = :userId "
            . "AND c.status = 1 ",
            ChatModel::tableName(),
            UserChatModel::tableName()
        );

        return self::getDb()->createCommand($query, [':userId' => $userId])->queryAll();
    }

    public function saveChat(?string $name, ?bool $isChannel, ?array $userIdList, ?int $currentUserId): ?int
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            $chatParams = [
                'name' => $name,
                'status' => self::STATUS_ENABLED,
            ];
            if (!empty($isChannel)) {
                $chatParams['isChannel'] = 1;
            }

            $newChatId = $this->insertBy($chatParams);
            if (empty($newChatId)) {
                $transaction->rollBack();
            } else {
                UserChatModel::getInstance()->saveUserChat($currentUserId, $newChatId, UserChatModel::IS_CHAT_OWNER_YES);
                foreach ($userIdList as $userId) {
                    UserChatModel::getInstance()->saveUserChat($userId, $newChatId, UserChatModel::IS_CHAT_OWNER_NO);
                }
                $transaction->commit();
            }
        } catch (Exception $ex) {
            $newChatId = null;
            $transaction->rollBack();
            $this->addError(self::DEFAULT_ERR_ATTRIBUTE, 'Ошибка: ' . $ex->getMessage());
        }

        return $newChatId ?? null;
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
        $chatCountList = ChatMessageModel::getInstance()->getChatListMsgCount($chatIds, $userId);
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
