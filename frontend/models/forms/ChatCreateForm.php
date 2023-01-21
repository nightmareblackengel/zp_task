<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use Exception;
use Yii;

class ChatCreateForm extends Form
{
    public ?int $id = null;
    public ?int $currentUserId = null;
    public ?string $name = null;
    public $isChannel = null;

    public $userIdList = null;

    public function rules()
    {
        return [
            [['isChannel', 'currentUserId', 'userIdList'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'isUniqueName'],
            [['isChannel', 'currentUserId'], 'integer'],
            [['userIdList'], 'safe'],
            [['userIdList'], 'customChannelUserCount'],
            [['userIdList'], 'checkIfPrivateChatExists'],
            [['name'], 'checkChannelNameForChannel', 'skipOnEmpty' => false],
        ];
    }

    public function isUniqueName($attribute)
    {
        if (empty($this->name)) {
            return;
        }

        $chatItem = ChatModel::getInstance()->getItemBy(['name' => $this->name]);
        if (!empty($chatItem)) {
            $this->addError($attribute, 'Данное имя уже занято! Выберите другое имя чата');
        }
    }

    public function checkChannelNameForChannel($attribute)
    {
        if (empty($this->isChannel)) {
            return;
        }
        if (empty($this->name)) {
            $this->addError($attribute, 'Необходимо заполнить название канала.');
        }
    }

    public function checkIfPrivateChatExists($attribute)
    {
        if (!empty($this->isChannel)) {
            return;
        }
        if (empty($this->userIdList)) {
            return;
        }
        $otherUser = $this->userIdList[0];

        if (UserChatModel::getInstance()->isUsersHasPrivateChat($this->currentUserId, $otherUser)) {
            $this->addError($attribute, 'Между этими пользователями уже есть чат.');
        }
    }

    public function customChannelUserCount($attribute)
    {
        if (empty($this->userIdList) || !is_array($this->userIdList)) {
            return null;
        }

        if (empty($this->isChannel) && count($this->userIdList) !== 1) {
            $this->addError(
                $attribute,
                'Для "приватного чата" можно выбирать только одного пользователя.'
            );
        }
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название канала',
            'userIdList' => 'Список пользователей',
            'isChannel' => 'Является каналом',
        ];
    }

    public function load($data, $formName = null)
    {
        $parentRes = parent::load($data, $formName);

        $userData = Yii::$app->controller->getCurrentUser();
        $this->currentUserId = $userData['id'] ?? null;

        return $parentRes;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        return ChatModel::getInstance()
            ->saveChat($this->name, $this->isChannel, $this->userIdList, $this->currentUserId);
    }
}
