<?php
namespace common\ext;

use Yii;
use yii\redis\Session;

class RedisSession extends Session
{
    public $keyPrefix = 'keyPrefix';

    protected function calculateKey($id)
    {
        return $this->keyPrefix . '_x_' . $id;
    }

    public function getId()
    {
        return base64_encode(Yii::$app->security->generateRandomKey(1024));
    }
}
