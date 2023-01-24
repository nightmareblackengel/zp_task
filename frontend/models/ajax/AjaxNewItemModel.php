<?php

namespace frontend\models\ajax;

use common\models\ChatMessageModel;
use frontend\models\forms\ChatMessageForm;
use frontend\models\helpers\AjaxHelper;
use Yii;

class AjaxNewItemModel extends AjaxBase
{
    public function load(?array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $this->showInResponse = (int) $data['show_in_response'] ?? 0;

        return true;
    }

    public function prepareResponse(?int $userId, ?int $chatId): ?array
    {
        $formModel = new ChatMessageForm();
        # TODO Fully incorrect
        if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            if (ChatMessageModel::getInstance()->saveMessageFrom($formModel)) {
                # TODO: replace
                echo "REDIRECTED!!!";
                exit();
                //return $this->redirect(Url::to(['/chat/index', 'chat_id' => $formModel->chatId]));
            }
            $formModel->addError('message', 'Unknown error!');
        }

        if ($this->showInResponse === AjaxHelper::AJAX_REQUEST_EXCLUDE) {
            return null;
        }

        return [
            'result' => AjaxHelper::AJAX_RESPONSE_OK,
            'html' => Yii::$app->controller->render('/chat/ajax/create-message', [
                'formModel' => $formModel,
            ]),
        ];
    }
}
