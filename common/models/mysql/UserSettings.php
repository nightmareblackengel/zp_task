<?php
namespace common\models\mysql;

use common\ext\db\ActiveRecord;

class UserSettings extends ActiveRecord
{
    public static function tableName()
    {
        return 'user_setting';
    }

    public function rules()
    {
        return [
            [['userId', 'historyStoreType', 'historyStoreTime'], 'integer'],
            [['userId', 'historyStoreType', 'historyStoreTime'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'userId',
            'historyStoreType',
            'historyStoreTime',
        ];
    }
}
