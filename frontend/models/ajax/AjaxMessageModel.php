<?php

namespace frontend\models\ajax;

use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use common\models\mysql\UserModel;
use frontend\models\helpers\AjaxHelper;
use Yii;

class AjaxMessageModel extends AjaxBase
{
    public ?int $lastUpdatedAt = null;

    public function load(?array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $this->lastUpdatedAt = (int) $data['last_updated_at'] ?? 0;
        $this->showInResponse = (int) $data['show_in_response'] ?? 0;

        return true;
    }

    public function prepareResponse(?int $userId, ?int $chatId, ?array $params = []): ?array
    {
        if ($this->showInResponse === AjaxHelper::AJAX_REQUEST_EXCLUDE) {
            return null;
        }
        if (empty($chatId)) {
            return [
                'result' => AjaxHelper::AJAX_RESPONSE_OK,
                'messages_count' => false,
                'html' => Yii::$app->controller->render('/chat/ajax/messages_empty'),
            ];
        }
        $messages = ChatMessageModel::getInstance()
            // TODO: replace it count on userSettings
            ->getList($chatId, 0, 1999);
        $chat = ChatModel::getInstance()->getItemBy(['id' => $chatId]);
        $chatOwnerId = UserChatModel::getInstance()->getChatOwnerId($chatId);

        return [
            'result' => AjaxHelper::AJAX_RESPONSE_OK,
            'messages_count' => is_array($messages) ? count($messages) : false,
            'html' => Yii::$app->controller->render('/chat/ajax/messages', [
                'userList' => UserModel::getInstance()->getUserListForChat($chatId),
                'messages' => $messages,
                'currentUserId' => $userId,
                'chat' => $chat,
                'chatOwnerId' => $chatOwnerId,
            ]),
        ];
    }
}
