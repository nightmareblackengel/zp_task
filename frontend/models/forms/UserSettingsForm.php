<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\mysql\UserSettingsModel;

class UserSettingsForm extends Form
{
    public ?int $historyStoreType = 0;

    public ?int $historyStoreTime = 0;

    public ?int $userId = 0;

    protected ?bool $isNewRecord = null;

    public function rules()
    {
        return [
            [['historyStoreType', 'historyStoreTime'], 'required'],
            [['historyStoreType', 'historyStoreTime'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'userId' => 'Id пользователя',
            'historyStoreType' => 'Тип хранения истории',
            'historyStoreTime' => 'Кол-во элементов, для хранения истории',
        ];
    }

    public function loadFromDb(): bool
    {
        $item = UserSettingsModel::getInstance()
            ->getItemBy(['userId' => $this->userId]);

        $this->isNewRecord = empty($item);
        if (empty($item)) {
            return false;
        }

        $this->historyStoreType = $item['historyStoreType'] ?? 0;
        $this->historyStoreTime = $item['historyStoreTime'] ?? 0;

        return $this->validate();
    }

    public function save(): bool
    {
        if ($this->isNewRecord) {
            $saveRes = UserSettingsModel::getInstance()->insertBy($this->getAttributes());
        } else {
            $values = $this->getAttributes();
            unset($values['userId']);
            UserSettingsModel::getInstance()->updateBy($values, ['userId' => $this->userId]);
            $saveRes = true;
        }

        return !empty($saveRes) ? true : false;
    }
}
