<?php

namespace frontend\models\service;

use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use common\models\mysql\UserModel;
use common\models\redis\ChatDateTimeMhashStorage;
use frontend\models\ChatMessageModel;
use yii\db\Expression;
use yii\db\Query;

class UserChatService
{
    public function getCombinedChatList(int $userId): array
    {
        $channelList = $this->getChannelChatList($userId);
        $notChannelList = $this->getNotChannelChatList($userId);

        $result = [];
        if (!empty($channelList)) {
            foreach ($channelList as $channelItem) {
                $chatId = $channelItem['chatId'];
                $result[$chatId] = $channelItem;
            }
        }
        if (!empty($notChannelList)) {
            foreach ($notChannelList as $notChannelItem) {
                $chatId = $notChannelItem['chatId'];
                $result[$chatId] = $notChannelItem;
            }
        }
        if (empty($result)) {
            return [];
        }
        $chatIds = array_keys($result);
        // get count for all chats
        $chatCountList = ChatMessageModel::getInstance()
            ->getChatListMsgCount($chatIds);
        // get last chat datetime
        $dates = ChatDateTimeMhashStorage::getInstance()
            ->getChatLastDtList($chatIds);

        foreach ($result as $chatId => $chatItem) {
            $result[$chatId]['count'] = $chatCountList[$chatId] ?? 0;
            $result[$chatId]['ldt'] = $dates[$chatId] ?? 0;
        }
        uasort($result, function($item1, $item2) {
            if ($item1['ldt'] === $item2['ldt']) {
                return 0;
            }

            return $item1['ldt'] > $item2['ldt'] ? -1 : 1;
        });

        return $result;
    }

    public function getChannelChatList(int $userId): array
    {
        $query = new Query();
        $query->select([
                'uc.chatId',
                new Expression(sprintf("'%s' AS `isChannel`", ChatModel::IS_CHANNEL_TRUE)),
                'c.name',
            ])
            ->from(['uc' => UserChatModel::tableName()])
            ->innerJoin(['c' => ChatModel::tableName()], 'c.id = uc.chatId')
            ->where([
                'uc.userId' => $userId,
                'c.isChannel' => ChatModel::IS_CHANNEL_TRUE,
            ])
            ->groupBy('uc.chatId');

        return $query->all();
    }

    public function getNotChannelChatList(int $userId): array
    {
        $subQuery = new Query();
        $subQuery->select([
                'uc2.`chatId`'
            ])
            ->from(['uc2' => UserChatModel::tableName()])
            ->where(['uc2.`userId`' => $userId]);

        $query = new Query();
        $query->select([
                'uc.chatId',
                new Expression(sprintf("'%s' AS `isChannel`", ChatModel::IS_CHANNEL_FALSE)),
                'name' => "u.email",
            ])->from(['uc' => UserChatModel::tableName()])
            ->innerJoin(['c' => ChatModel::tableName()], 'c.id = uc.chatId')
            ->innerJoin(['u' => UserModel::tableName()], 'uc.userId = u.id')
            ->where([
                'c.isChannel' => ChatModel::IS_CHANNEL_FALSE
            ])
            ->andWhere(['!=', 'uc.userId',  $userId])
            ->andWhere(['IN', 'uc.chatId', $subQuery])
            ->groupBy('uc.chatId, u.email');

        return $query->all();
    }
}
