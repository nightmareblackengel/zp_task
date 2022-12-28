<?php

namespace common\models\mysql;

use common\ext\db\ActiveRecord;

class UserChat extends ActiveRecord
{
    public static function tableName()
    {
        return '`user_chat`';
    }
}
