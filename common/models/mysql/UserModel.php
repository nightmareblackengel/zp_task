<?php

namespace common\models\mysql;

use common\ext\base\MySqlModel;

class UserModel extends MySqlModel
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    public static function tableName(): string
    {
        return '`user`';
    }
}
