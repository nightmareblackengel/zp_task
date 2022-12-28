<?php

namespace common\models;

use common\ext\base\Model;
use common\models\mysql\UserChat;

class UserChatModel extends Model
{
    const IS_USER_BANNED_NO = 0;
    const IS_USER_BANNED_YES = 1;

    public $model = UserChat::class;
}
