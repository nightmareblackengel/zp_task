<?php

namespace frontend\models\forms;

use common\ext\base\Form;

class ChatMessageForm extends Form
{
    public ?string $message = null;

    public function rules()
    {
        return [
            [['message'], 'required'],
            [['message'], 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'message' => 'Сообщение',
        ];
    }
}
