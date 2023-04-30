<?php
namespace frontend\models\mysql;

use common\models\mysql\UserChatModel;
use frontend\models\service\UserChatService;
use Yii;
use yii\db\Query;

class ChatModel extends \common\models\mysql\ChatModel
{
    public static function prepareChatListWithCount(int $userId): array
    {
        $chatList = Yii::$app->cache->getOrSet('chat:list:'. $userId, function() use($userId) {
            $ucService = new UserChatService();
            return $ucService->getCombinedChatList($userId);
        }, 5);
        if (empty($chatList)) {
            return [];
        }

        return $chatList;
    }

    public function getChannelList(int $userId, string $searchText, int $limit = 20): array
    {
        $subQuery = new Query();
        $subQuery->select(['`chatId`'])
            ->from(['uc' => UserChatModel::tableName()])
            ->where(['userId' => $userId]);

        $query = new Query();
        $query->select([
            'text' => 'c.name',
            'c.id',
        ])->from(['c' => self::tableName()])
            ->where([
                '[[c.isChannel]]' => self::IS_CHANNEL_TRUE,
            ])
            ->andWhere(['NOT IN', '[[c.id]]', $subQuery])
            ->andWhere(['LIKE', '[[c.name]]', $searchText])
            ->limit($limit);

        $list = $query->all();
        if (empty($list)) {
            return [];
        }

        return $list;
    }
}
