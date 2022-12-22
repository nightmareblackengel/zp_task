<?php

namespace frontend\models\helpers;

use common\ext\patterns\Singleton;
use common\models\redis\CookieSetStorage;
use yii\base\BaseObject;

class AuthStorageHelper extends BaseObject
{
    use Singleton;

    /** @var CookieSetStorage */
    protected $cookieStorage;

    public function init()
    {
        parent::init();
        $this->cookieStorage = new CookieSetStorage();
    }

    public function getValue($key)
    {
        return $this->cookieStorage->getValue($key);
    }

    public function delete($key)
    {
        return $this->cookieStorage->removeKey($key);
    }
    
    public function save($key, $data, $timeout = 0)
    {
        return $this->cookieStorage->setExValue($key, $data, $timeout);
    }

}
