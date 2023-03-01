<?php

namespace frontend\models\forms;

use common\ext\base\Form;

class ChatAddUserForm extends Form
{
    public $chatId;
    public $userIds;

    public function rules()
    {
        return [
            [['chatId'], 'integer'],
            ['userIds', 'safe'],
        ];
    }

    public function load($data, $formName = null)
    {
        $parentRes = parent::load($data, $formName);

        return $parentRes;
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }

        return true;
    }
}
