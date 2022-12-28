<?php

namespace frontend\models\forms;

use common\ext\base\Form;

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
            [['isChannel', 'currentUserId'], 'integer'],
            [['userIdList'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'userIdList' => 'Список пользователей',
            'isChannel' => 'Является каналом',
        ];
    }
}
