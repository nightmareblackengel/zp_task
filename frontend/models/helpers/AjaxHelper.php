<?php

namespace frontend\models\helpers;

use common\ext\traits\ErrorTrait;
use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use common\models\mysql\UserModel;
use frontend\models\forms\ChatMessageForm;
use Yii;

class AjaxHelper
{
    use ErrorTrait;

    const DEFAULT_ERR_ATTRIBUTE = '-';

    const AJAX_RESPONSE_OK = 1;
    const AJAX_RESPONSE_NOT_FILLED = 2;
    const AJAX_RESPONSE_ERR = 3;

    const AJAX_REQUEST_INCLUDE = 1;
    const AJAX_REQUEST_EXCLUDE = 2;

    public ?int $userId = null;

    public ?int $chatId = null;
    public ?int $chatShowInResponse = null;
    public ?int $chatLastUpdatedAt = null;

    public ?int $msgLastUpdatedAt = null;
    public ?int $msgShowInResponse = null;

    public ?int $newItemShowInResponse = null;

    public function load($data, $formName = null)
    {
        $currentUser = Yii::$app->controller->getCurrentUser();
        if (empty($currentUser)) {
            $this->addError('Ошибка! данный пользователь не найден.');
            return false;
        }
        $this->userId = $currentUser['id'];

        if (!empty($data['chats'])) {
            $this->chatId = (int) $data['chats']['id'] ?? 0;
            $this->chatLastUpdatedAt = (int) $data['chats']['last_updated_at'] ?? 0;
            $this->chatShowInResponse = (int) $data['chats']['show_in_response'] ?? 0;
        }
        if (!empty($data['messages'])) {
            $this->msgLastUpdatedAt = (int) $data['messages']['last_updated_at'] ?? 0;
            $this->msgShowInResponse = (int) $data['messages']['show_in_response'] ?? 0;
        }
        if (!empty($data['new_item'])) {
            $this->newItemShowInResponse = (int) $data['new_item']['show_in_response'] ?? 0;
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
                # TODO: replace
                echo "REDIRECTED!!!";
                exit();
                //return $this->redirect(Url::to(['/chat/index', 'chat_id' => $formModel->chatId]));
            }
            $formModel->addError('message', 'Unknown error!');
        }
        //
        $resultArr = ['result' => self::AJAX_RESPONSE_OK];

        if ($this->chatShowInResponse === self::AJAX_REQUEST_INCLUDE) {
            $resultArr['chats'] = [
                'result' => self::AJAX_RESPONSE_OK,
                'html' => Yii::$app->controller->render('/chat/ajax/chats', [
                    'chatList' => ChatModel::prepareChatListWithCount($this->userId),
                    'requestChatId' => $this->chatId,
                ]),
                'downloaded_at' => time(),
            ];
        }

        if ($this->msgShowInResponse === self::AJAX_REQUEST_INCLUDE) {
            $resultArr['messages'] = [
                'result' => self::AJAX_RESPONSE_OK,
                'chat_id' => $this->chatId ?? 0,
                'show_add_new_message' => is_array($messages) ? count($messages) : 0,
                'html' => Yii::$app->controller->render('/chat/ajax/messages', [
                    'userList' => UserModel::getInstance()->getUserListForChat($this->chatId),
                    'messages' => $messages,
                    'currentUserId' => $this->userId,
                ]),
                'downloaded_at' => time(),
            ];
        }

        if ($this->newItemShowInResponse === self::AJAX_REQUEST_INCLUDE) {
            $resultArr['new_message'] = [
                'result' => self::AJAX_RESPONSE_OK,
                'html' => Yii::$app->controller->render('/chat/ajax/create-message', [
                    'formModel' => $formModel,
                ]),
            ];
        }

        return $resultArr;
    }

    public function getDefaultError()
    {
        if (empty($this->_errors[self::DEFAULT_ERR_ATTRIBUTE])) {
            return '';
        }

        return array_shift($this->_errors[self::DEFAULT_ERR_ATTRIBUTE]);
    }
}
