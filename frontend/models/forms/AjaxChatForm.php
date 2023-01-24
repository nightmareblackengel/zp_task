<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use common\models\mysql\UserModel;
use frontend\ext\helpers\Url;
use Yii;

class AjaxChatForm extends Form
{
    const DEFAULT_ERR_ATTRIBUTE = '-';

    const AJAX_RESULT_OK = 1;
    const AJAX_RESULT_NOT_FILLED = 2;
    const AJAX_RESULT_ERR = 3;

    public ?int $userId = null;
    public ?int $chatId = null;
    public ?int $chatLastUpdatedAt = null;
    public ?int $msgLastUpdatedAt = null;

    public function load($data, $formName = null)
    {
        if (!empty($data['chats'])) {
            $this->chatId = (int) $data['chats']['id'] ?? 0;
            $this->chatLastUpdatedAt = (int) $data['chats']['lastUpdatedAt'] ?? 0;
        }
        if (!empty($data['messages'])) {
            $this->msgLastUpdatedAt = (int) $data['messages']['lastUpdatedAt'];
        }

        return true;
    }

    public function hasAccess()
    {
        if (!empty($this->chatId)) {
            $hasAccess = UserChatModel::getInstance()->isUserBelongToChat($this->userId, $this->chatId);
            if (!$hasAccess) {
                $this->addError(self::DEFAULT_ERR_ATTRIBUTE, 'Ошибка 403! У Вас нет доступа к этому чату');
                return false;
            }
        }

        return true;
    }

    public function prepareData(): array
    {
        // messages
        $messages = false;
        if (!empty($this->chatId)) {
            $messages = ChatMessageModel::getInstance()
                ->getList($this->chatId, 0, 2000);
        }
        // add new message
        $formModel = new ChatMessageForm();
        if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            if (ChatMessageModel::getInstance()->saveMessageFrom($formModel)) {
                return $this->redirect(Url::to(['/chat/index', 'chat_id' => $formModel->chatId]));
            }
            $formModel->addError('message', 'Unknown error!');
        }
        //

        return [
            'result' => self::AJAX_RESULT_OK,
            'chats' => [
                'result' => self::AJAX_RESULT_OK,
                'html' => Yii::$app->controller->render('/chat/ajax/chats', [
                    'chatList' => ChatModel::prepareChatListWithCount($this->userId),
                    'requestChatId' => $this->chatId,
                ]),
                'downloaded_at' => time(),
            ],
            'messages' => [
                'result' => self::AJAX_RESULT_OK,
                'chat_id' => $this->chatId ?? 0,
                'show_add_new_message' => is_array($messages) ? count($messages) : 0,
                'html' => Yii::$app->controller->render('/chat/ajax/messages', [
                    'userList' => UserModel::getInstance()->getUserListForChat($this->chatId),
                    'messages' => $messages,
                    'currentUserId' => $this->userId,
                ]),
                'downloaded_at' => time(),
            ],
            'new_message' => [
                'result' => self::AJAX_RESULT_OK,
                'html' => Yii::$app->controller->render('/chat/ajax/create-message', [
                    'formModel' => $formModel,
                ]),
            ],
        ];
    }
}
