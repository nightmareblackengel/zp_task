<?php

namespace common\models\mysql;

use common\ext\db\ActiveRecord;

class Chat extends ActiveRecord
{
    public static function tableName()
    {
        return '`chat`';
    }

    public function rules()
    {
        return [
            [['isChannel', 'status', 'name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['id', 'isChannel', 'status'], 'integer'],
        ];
    }
}
