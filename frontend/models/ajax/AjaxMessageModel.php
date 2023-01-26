<?php

namespace frontend\models\ajax;

use common\models\ChatMessageModel;
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

    public function prepareResponse(?int $userId, ?int $chatId): ?array
    {
        if ($this->showInResponse === AjaxHelper::AJAX_REQUEST_EXCLUDE) {
            return null;
        }
        $messages = false;
        if (!empty($chatId)) {
            $messages = ChatMessageModel::getInstance()
                ->getList($chatId, 0, 2000);
        }

        return [
            'result' => AjaxHelper::AJAX_RESPONSE_OK,
            'show_add_new_message' => is_array($messages) ? count($messages) : false,
            'html' => Yii::$app->controller->render('/chat/ajax/messages', [
                'userList' => UserModel::getInstance()->getUserListForChat($chatId),
                'messages' => $messages,
                'currentUserId' => $userId,
            ]),
            'downloaded_at' => time(),
        ];
    }
}
