<?php

namespace frontend\models\forms;

use common\ext\base\Form;

class ChatCreateForm extends Form
{
    public ?int $userId = null;
    public ?string $name = null;
    public $isChannel = null;
    public $status = null;

    public function rules()
    {
        return [
            [['isChannel', 'status', 'name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['id', 'isChannel', 'status'], 'integer'],
        ];
    }
}
