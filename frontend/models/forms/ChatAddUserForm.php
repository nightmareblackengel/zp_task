<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\ext\helpers\Html;
use common\models\ChatMessageModel;
use common\models\mysql\UserChatModel;

class ChatAddUserForm extends Form
{
    public $chatId;
    public $userIds;
    public $userCanAddIds = [];
    public $existsUsers = [];

    public function rules()
    {
        return [
            [['userIds', 'chatId'], 'required'],
            [['chatId'], 'integer'],
            ['userIds', 'addUserValidation'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'userIds' => 'Список пользователей',
        ];
    }

    public function addUserValidation($attribute)
    {
        if (empty($this->userCanAddIds)) {
            $this->addError($attribute, 'Список новых пользователей пуст, Вы не можете выполнить сохранение.');
            return;
        }
        if (empty($this->userIds)) {
            $this->addError($attribute, 'Список добавленных пользователей пуст. Вы не можете выполнить сохранение.');
            return;
        }
        $userIds = $this->$attribute;
        $allowedUserIds = array_keys($this->userCanAddIds);
        foreach ($userIds as $userId) {
            $userId = (int) $userId;
            if (!in_array($userId, $allowedUserIds)) {
                $this->addError($attribute, 'Вы не можете добавить пользователя ' . Html::encode($this->existsUsers[$userId] ?? '-'));
            }
        }
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        foreach ($this->userIds as $userId) {
            UserChatModel::getInstance()->saveUserChat($userId, $this->chatId, UserChatModel::IS_CHAT_OWNER_NO);
            // добавление сообщений в чат, о том, что эти пользователи были добавлены
            ChatMessageModel::getInstance()->insertMessage(
                $userId, $this->chatId, 'Добавлен пользователь ' . $this->userCanAddIds[$userId] ?? '-', ChatMessageModel::MESSAGE_TYPE_SYSTEM
            );
        }

        return true;
    }
}
