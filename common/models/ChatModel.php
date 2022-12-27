<?php

namespace common\models;

use common\ext\base\Model;
use common\models\mysql\Chat;

class ChatModel extends Model
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    const IS_CHANNEL_FALSE = 0;
    const IS_CHANNEL_TRUE = 1;

    public $model = Chat::class;
}
