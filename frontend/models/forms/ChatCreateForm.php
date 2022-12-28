<?php

namespace frontend\models\forms;

use common\ext\base\Form;
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
            [['isChannel', 'currentUserId'], 'integer'],
            [['userIdList'], 'safe'],
            [['userIdList'], 'customChannelUserCount'],
        ];
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


        return false;
    }
}
