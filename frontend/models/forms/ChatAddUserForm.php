<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\ext\helpers\Html;
use common\models\ChatMessageModel;
use common\models\mysql\UserChatModel;
use common\models\mysql\UserModel;

class ChatAddUserForm extends Form
{
    public $chatId;
    public $userIds;
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
        if (empty($this->userIds)) {
            $this->addError($attribute, 'Список добавленных пользователей пуст. Вы не можете выполнить сохранение.');
            return;
        }
        $userIds = $this->$attribute;
        $existAddedUserIds = UserModel::getInstance()->getList(['id' => $userIds], 'id');
        $allowedUserIds = array_column($existAddedUserIds, 'id');

        if (empty($allowedUserIds)) {
            $this->addError($attribute, 'Список новых пользователей пуст, Вы не можете выполнить сохранение.');
            return;
        }
        if (count($userIds) != count($allowedUserIds)) {
            $this->addError($attribute, 'Количество выбранных Вами пользователей и количество пользователей в БД - не совпадают');
            return;
        }

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
            $user = UserModel::getInstance()->getItemBy(['id' => $userId], 'id, email');
            ChatMessageModel::getInstance()->insertMessage(
                $userId, $this->chatId, 'Добавлен пользователь [' . $user['email'] ?? $userId . '] ', ChatMessageModel::MESSAGE_TYPE_SYSTEM
            );
        }

        return true;
    }
}
