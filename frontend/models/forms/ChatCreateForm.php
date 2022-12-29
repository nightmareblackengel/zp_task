<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use Exception;
use Yii;

class ChatCreateForm extends Form
{
    public ?int $currentUserId = null;
    public ?string $name = null;
    public $isChannel = null;

    public $userIdList = null;

    public function rules()
    {
        return [
            [['isChannel', 'name', 'currentUserId', 'userIdList'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'isUniqueName'],
            [['isChannel', 'currentUserId'], 'integer'],
            [['userIdList'], 'safe'],
            [['userIdList'], 'customChannelUserCount'],
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

        return;
    }

    public function customChannelUserCount($attribute)
    {
        if (empty($this->userIdList) || !is_array($this->userIdList)) {
            return null;
        }

        if (empty($this->isChannel) && count($this->userIdList) !== 1) {
            $this->addError(
                $attribute,
                'Для типа чата "Не Является каналом" можно добавлять только одного пользователя.'
            );
        }
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
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

        $result = false;
        $transaction = ChatModel::getDb()->beginTransaction();
        try {
            $chatParams = [
                'name' => $this->name,
                'status' => ChatModel::STATUS_ENABLED,
            ];
            if (!empty($this->isChannel)) {
                $chatParams['isChannel'] = 1;
            }

            $chatId = ChatModel::getInstance()->insertBy($chatParams);
            if (empty($chatId)) {
                $transaction->rollBack();
                $this->addError('name', 'Ошибка. Чат не сохранён!');
            } else {
                $this->saveUserChat($this->currentUserId, $chatId, UserChatModel::IS_CHAT_OWNER_YES);
                foreach ($this->userIdList as $userId) {
                    $this->saveUserChat($userId, $chatId, UserChatModel::IS_CHAT_OWNER_NO);
                }

                $transaction->commit();
                $result = true;
            }
        } catch (Exception $ex) {
            $transaction->rollBack();
            $this->addError('name', 'Ошибка. Дополнительные данные чата не сохранились.');
        }

        return $result;
    }

    protected function saveUserChat($userId, $chatId, $isOwner)
    {
        $userChatParams = [
            'userId' => $userId,
            'chatId' => $chatId,
        ];
        if (!empty($isOwner)) {
            $userChatParams['isChatOwner'] = UserChatModel::IS_CHAT_OWNER_YES;
        }

        return UserChatModel::getInstance()->insertBy($userChatParams);
    }
}
