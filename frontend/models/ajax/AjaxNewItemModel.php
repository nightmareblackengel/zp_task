<?php

namespace frontend\models\ajax;

use common\models\ChatMessageModel;
use common\models\mysql\UserChatModel;
use frontend\models\forms\MessageAddForm;
use frontend\models\helpers\AjaxHelper;
use Yii;

class AjaxNewItemModel extends AjaxBase
{
    public function load(?array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $this->showInResponse = (int) ($data['show_in_response'] ?? 0);

        return true;
    }

    public function prepareResponse(?int $userId, ?int $chatId, ?array $params = []): ?array
    {
        if ($this->showInResponse === AjaxHelper::AJAX_REQUEST_EXCLUDE) {
            return null;
        }

        $formModel = new MessageAddForm();
        $formModel->chatId = $chatId;
        $formModel->userId = $userId;
        $formModel->messageType = ChatMessageModel::MESSAGE_TYPE_SIMPLE;
        $formModel->message = '';

        return [
            'result' => AjaxHelper::AJAX_RESPONSE_OK,
            'html' => Yii::$app->controller->render('/chat/ajax/create-message', [
                'formModel' => $formModel,
                'isUserBanned' => $params['isUserBanned'] ?? UserChatModel::IS_USER_BANNED_NO,
            ]),
        ];
    }
}
