<?php

namespace frontend\models\helpers;

use common\ext\patterns\Singleton;
use common\models\redis\CookieSetStorage;
use common\models\redis\UserIdSetStorage;
use yii\base\BaseObject;

/**
 * все данных будут храниться в двух хранилищах
 * 1. хранилище "кука" - "Id пользователя"
 * 2. хранилище "Id пользователя" - "кука"
 */
class AuthStorageHelper extends BaseObject
{
    use Singleton;

    /** @var CookieSetStorage */
    protected $cookieStorage;

    /** @var UserIdSetStorage */
    protected $userStorage;

    public function init()
    {
        parent::init();
        $this->cookieStorage = new CookieSetStorage();
        $this->userStorage = new UserIdSetStorage();
    }

    public function getValue($key)
    {
        return $this->cookieStorage->getValue($key);
    }

    public function delete($key, $userId, $clearUserId = true)
    {
        if ($clearUserId) {
            $this->userStorage->removeKey($userId);
        }

        return $this->cookieStorage->removeKey($key);
    }
    
    public function save($userId, $key, $data, $timeout = 0)
    {
        $this->userStorage->setExValue($userId, $this->prepareKey($key), $timeout);

        return $this->cookieStorage->setExValue($key, $data, $timeout);
    }

    public function validateByLastAuth(string $key, int $userId): bool
    {
        $userValue = $this->userStorage->getValue($userId);
        if (empty($userValue)) {
            return false;
        }
        $md5 = md5($key);

        return $md5 === $userValue;
    }

    protected function prepareKey($key)
    {
        return md5($key);
    }
}
