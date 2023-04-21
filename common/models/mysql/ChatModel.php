<?php

namespace common\models\mysql;

use common\ext\base\MySqlModel;
use common\models\redis\ChatDateTimeMhashStorage;
use Exception;
use frontend\models\service\UserChatService;

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
                if (!empty($userIdList)) {
                    foreach ($userIdList as $userId) {
                        UserChatModel::getInstance()->saveUserChat($userId, $newChatId, UserChatModel::IS_CHAT_OWNER_NO);
                    }
                }
                $transaction->commit();
            }
        } catch (Exception $ex) {
            $newChatId = null;
            $transaction->rollBack();
            $this->addError(self::DEFAULT_ERR_ATTRIBUTE, 'Ошибка: ' . $ex->getMessage());
        }
        ChatDateTimeMhashStorage::getInstance()
            ->setValue(null, $newChatId, time());

        return $newChatId ?? null;
    }

    public static function prepareChatListWithCount(int $userId): array
    {
        $ucService = new UserChatService();
        $chatList = $ucService->getCombinedChatList($userId);
        if (empty($chatList)) {
            return [];
        }

        return $chatList;
    }
}
