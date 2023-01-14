<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\ChatMessageModel;
use Yii;

class ChatMessageForm extends Form
{
    public ?string $message = null;
    public ?int $userId = null;
    public ?int $chatId = null;
    public ?int $messageType = null;

    public function rules()
    {
        return [
            [['message', 'chatId', 'userId', 'messageType'], 'required'],
            [['message'], 'string'],
            [['chatId', 'userId', 'messageType'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'message' => 'Сообщение',
        ];
    }

    public function load($data, $formName = null)
    {
        $res = parent::load($data, $formName);

        $this->chatId = Yii::$app->request->get('chat_id');
        if (empty($this->messageType)) {
            $this->messageType = ChatMessageModel::MESSAGE_TYPE_SIMPLE;
        }
        $user = Yii::$app->controller->getCurrentUser();
        $this->userId = $user['id'];

        return $res;
    }
}
