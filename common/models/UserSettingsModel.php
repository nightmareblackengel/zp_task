<?php

namespace common\models;

use common\ext\base\Model;
use common\models\mysql\UserSettings;

class UserSettingsModel extends Model
{
    const HISTORY_STORE_TYPE_DAY = 1;
    const HISTORY_STORE_TYPE_MESSAGE = 2;

    public $model = UserSettings::class;
}
