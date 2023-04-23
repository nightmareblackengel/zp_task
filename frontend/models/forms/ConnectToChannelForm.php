<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use Yii;

class ConnectToChannelForm extends Form
{
    public ?int $currentUserId = null;
    public ?int $channelId = null;

    public function rules()
    {
        return [
            [['currentUserId', 'channelId'], 'required'],
            [['currentUserId', 'channelId'], 'integer'],
            [['channelId'], 'channelExistsRule', 'skipOnEmpty' => true],
            [['currentUserId', 'channelId'], 'recordExistsAlready', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'currentUserId' => 'Текущий пользователь',
            'channelId' => 'Канал',
        ];
    }

    public function channelExistsRule($attribute)
    {
        if (!empty($this->$attribute)) {
            $channel = ChatModel::getInstance()->getItemBy(['id' => $this->$attribute]);
            if (empty($channel)) {
                $this->addError($attribute, 'Ошибка. Невозможно добавить этот чат, т.к. он не существует');
            }
        }
    }

    public function recordExistsAlready($attribute)
    {
        if (!empty($this->currentUserId) && !empty($this->channelId)) {
            $ucItem = UserChatModel::getInstance()->getItemBy([
                'userId' => $this->currentUserId,
                'chatId' => $this->channelId,
            ]);
            if (!empty($ucItem)) {
                $this->addError('channelId', 'Ошибка. Вы уже подключены к данному каналу.');
            }
        }
    }

    public function load($data, $formName = null)
    {
        $parentRes = parent::load($data, $formName);

        $userData = Yii::$app->controller->getCurrentUser();
        $this->currentUserId = $userData['id'] ?? null;

        return $parentRes;
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $saveRes = UserChatModel::getInstance()->insertBy([
            'userId' => $this->currentUserId,
            'chatId' => $this->channelId,
            'isUserBanned' => UserChatModel::IS_USER_BANNED_NO,
            'isChatOwner' => UserChatModel::IS_CHAT_OWNER_NO,
        ]);

        return true;
    }
}
