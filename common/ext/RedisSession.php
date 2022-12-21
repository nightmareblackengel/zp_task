<?php
namespace common\ext;

use Yii;
use yii\redis\Session;

class RedisSession extends Session
{
    protected function calculateKey($id)
    {
        return $this->keyPrefix . '_x_' . $id;
    }
}
