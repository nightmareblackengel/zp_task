<?php

namespace frontend\models\helpers;

use common\ext\patterns\Singleton;
use common\models\redis\CookieStringStorage;
use common\models\redis\UserIdStringStorage;
use yii\base\BaseObject;

/**
 * все данных будут храниться в двух хранилищах
 * 1. хранилище "кука" - "Id пользователя"
 * 2. хранилище "Id пользователя" - "кука"
 */
class AuthStorageHelper extends BaseObject
{
    use Singleton;

    /** @var CookieStringStorage */
    protected $cookieStorage;

    /** @var UserIdStringStorage */
    protected $userStorage;

    public function init()
    {
        parent::init();
        $this->cookieStorage = CookieStringStorage::getInstance();
        $this->userStorage = UserIdStringStorage::getInstance();
    }

    public function getValue($key)
    {
        return $this->cookieStorage->getValue($key);
    }

    public function delete($key, $userId, $clearUserId = true)
    {
        if ($clearUserId && !empty($userId)) {
            $this->userStorage->delete($userId);
        }

        return $this->cookieStorage->delete($key);
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
