<?php

namespace frontend\models\helpers;

use common\ext\traits\ErrorTrait;
use common\models\mysql\UserChatModel;
use frontend\models\ajax\AjaxChatModel;
use frontend\models\ajax\AjaxMessageModel;
use frontend\models\ajax\AjaxNewItemModel;
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

    public ?AjaxChatModel $chat = null;
    public ?AjaxMessageModel $message = null;
    public ?AjaxNewItemModel $newItem = null;

    public function __construct()
    {
        $this->chat = new AjaxChatModel();
        $this->message = new AjaxMessageModel();
        $this->newItem = new AjaxNewItemModel();
    }

    public function load($data)
    {
        $currentUser = Yii::$app->controller->getCurrentUser();
        if (empty($currentUser)) {
            $this->addError('Ошибка! данный пользователь не найден.');
            return false;
        }
        $this->userId = $currentUser['id'];

        $this->chat->load($data['chats'] ?? []);
        $this->message->load($data['messages'] ?? []);
        $this->newItem->load($data['new_item'] ?? []);

        return true;
    }

    public function hasAccess()
    {
        if (!empty($this->chat->id)) {
            $hasAccess = UserChatModel::getInstance()->isUserBelongToChat($this->userId, $this->chat->id);
            if (!$hasAccess) {
                $this->addError(self::DEFAULT_ERR_ATTRIBUTE, 'Ошибка 403! У Вас нет доступа к этому чату');
                return false;
            }
        }

        return true;
    }

    public function prepareData(): array
    {
        return [
            'result' => self::AJAX_RESPONSE_OK,
            'chat_id' => $this->chat->id,
            'send_time' => time(),
            'chats' => $this->chat->prepareResponse($this->userId, $this->chat->id),
            'messages' => $this->message->prepareResponse($this->userId, $this->chat->id),
            'new_message' => $this->newItem->prepareResponse($this->userId, $this->chat->id),
        ];
    }

    public function getDefaultError()
    {
        if (empty($this->_errors[self::DEFAULT_ERR_ATTRIBUTE])) {
            return '';
        }

        return array_shift($this->_errors[self::DEFAULT_ERR_ATTRIBUTE]);
    }
}
