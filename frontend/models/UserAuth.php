<?php
namespace frontend\models;

use Exception;
use frontend\models\helpers\AuthCookieHelper;
use frontend\models\helpers\AuthEncryptHelper;
use frontend\models\helpers\AuthStorageHelper;
use Yii;
use yii\base\InvalidValueException;
use yii\web\IdentityInterface;

class UserAuth extends \yii\web\User
{
    public $identityClass = UserAuthIdentity::class;

    public $enableAutoLogin = true;

    public $identityCookie = ['name' => AuthCookieHelper::COOK_AUTH, 'httpOnly' => true,];

    public $authTimeout = AuthCookieHelper::AUTH_TIMEOUT;

    protected $userIdentity = null;

    public function loginByAccessToken($token, $type = null)
    {
        echo 'loginByAccessToken<pre>';
        debug_print_backtrace();
        exit();
    }

    public function getId()
    {
        throw new Exception('Error UserAuth::getId not realized');
    }

    protected function renewAuthStatus()
    {
        echo "renewAuthStatus";
        exit();
    }
    protected function renewIdentityCookie()
    {
        echo "renewIdentityCookie";
        exit();
    }

    protected function sendIdentityCookie($identity, $duration)
    {
        echo 'sendIdentityCookie';
        exit();
    }
    protected function getIdentityAndDurationFromCookie()
    {
        echo "getIdentityAndDurationFromCookie";
        exit();
    }
    protected function loginByCookie()
    {
        echo 'loginByCookie<pre>';
        debug_print_backtrace();
        exit();
    }

    public function getIdentity($autoRenew = true)
    {
        if (!empty($this->userIdentity)) {
            return $this->userIdentity;
        }

        $authCook = AuthCookieHelper::getAuthCookie();
        if (empty($authCook->value)) {
            return AuthCookieHelper::removeAuthCookie();
        }

        $redisValue = AuthStorageHelper::getInstance()->getValue($authCook->value);
        if (empty($redisValue)) {
            return AuthCookieHelper::removeAuthCookie();
        }

        $redisAuthData = @unserialize($redisValue);
        if (empty($redisAuthData['auth'])) {
            AuthStorageHelper::getInstance()->delete($authCook->value);
            return AuthCookieHelper::removeAuthCookie();
        }

        $userId = AuthEncryptHelper::decode($authCook->value, $redisAuthData['auth'], 31);
        if (empty($userId)) {
            AuthStorageHelper::getInstance()->delete($authCook->value);
            return AuthCookieHelper::removeAuthCookie();
        }

        $this->userIdentity = UserAuthIdentity::findIdentity((int) $userId);
        if (empty($this->userIdentity)) {
            AuthStorageHelper::getInstance()->delete($authCook->value);
            return AuthCookieHelper::removeAuthCookie();
        }

        return $this->userIdentity ?? null;
    }

    public function setIdentity($identity)
    {
        $this->userIdentity = null;
        if (empty($identity)) {
            return null;
        }

        if (!$identity instanceof IdentityInterface) {
            throw new InvalidValueException('The identity object must implement IdentityInterface.');
        }

        $userData = $identity->getUser();
        if (empty($userData['id']) || empty($userData['email']) || empty($userData['created_at'])) {
            return null;
        }

        list($userIdHash, $userEmailHash) = $this->generateUserHashes($userData['id'], $userData['email'], $userData['created_at']);
        if (empty($userIdHash) || empty($userEmailHash)) {
            return null;
        }

        $redisKey = $userIdHash;
        $redisHashData = serialize([
            'auth' => $userEmailHash,
            'uip' => Yii::$app->request->getUserIP(),
        ]);
        $saveRes = AuthStorageHelper::getInstance()->save($redisKey, $redisHashData, $this->authTimeout);
        if (empty($saveRes)) {
            return null;
        }

        AuthCookieHelper::sendCookie($redisKey, $this->authTimeout, $this->identityCookie);
        $this->userIdentity = UserAuthIdentity::createFromParams($userData);

        return true;
    }

    public function switchIdentity($identity, $duration = 0)
    {
        $this->setIdentity($identity);
        if ($identity === null) {
            AuthCookieHelper::removeAuthCookie();
        }
    }

    # WARNING: do not remove this method: getIsGuest
    public function getIsGuest()
    {
        return $this->getIdentity() === null;
    }

    public function logout($destroySession = true)
    {
        $identity = $this->getIdentity();
        if ($identity !== null && $this->beforeLogout($identity)) {
            # remove redis key
            $authCook = AuthCookieHelper::getAuthCookie();
            if (!empty($authCook->value)) {
                AuthStorageHelper::getInstance()->delete($authCook->value);
            }
            // remove cookie
            $this->switchIdentity(null);
            // remove session
            if ($destroySession && $this->enableSession) {
                Yii::$app->getSession()->destroy();
            }
            $this->afterLogout($identity);
        }

        return $this->getIsGuest();
    }

    protected function generateUserHashes($userId, ?string $userEmail, ?string $userCreatedAt)
    {
        $userEmailHash  = AuthEncryptHelper::encode($userEmail, $userCreatedAt, 255);
        $userIdHash     = AuthEncryptHelper::encode($userId, $userEmailHash, 64);

        return [$userIdHash, $userEmailHash];
    }

}
