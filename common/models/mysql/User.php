<?php

namespace common\models\mysql;

use common\ext\db\ActiveRecord;

class User extends ActiveRecord
{
    public static function tableName()
    {
        return '`user`';
    }

    public function rules()
    {
        return [
            [['email'], 'required'],
            [['email'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 30],
            [['created_at'], 'safe'],
            [['status'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'name' => 'Name',
            'created_at' => 'Created At',
            'status' => 'Status',
        ];
    }
}
