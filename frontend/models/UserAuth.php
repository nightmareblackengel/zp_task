<?php
namespace frontend\models;

use frontend\ext\helpers\AuthCookieHelper;
use frontend\ext\helpers\AuthEncryptHelper;
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

        $redisValue = Yii::$app->redisDb2->get($authCook->value);
        if (empty($redisValue)) {
            return AuthCookieHelper::removeAuthCookie();
        }

        $redisAuthData = unserialize($redisValue);
        if (empty($redisAuthData['auth'])) {
            return AuthCookieHelper::removeAuthCookie();
        }

        $userId = AuthEncryptHelper::decode($authCook->value, $redisAuthData['auth'], 31);
        if (empty($userId)) {
            return AuthCookieHelper::removeAuthCookie();
        }

        $this->userIdentity = UserAuthIdentity::findIdentity((int) $userId);

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
        $resisHashData = serialize([
            'auth' => $userEmailHash,
            'session' => Yii::$app->session->getId(),
        ]);
        if (empty(Yii::$app->redisDb2->setex($redisKey, $this->authTimeout, $resisHashData))) {
            return null;
        }

        AuthCookieHelper::sendCookie($redisKey, $this->authTimeout, $this->identityCookie);
        $this->userIdentity = $this->userIdentity;

        return true;
    }

    protected function generateUserHashes($userId, ?string $userEmail, ?string $userCreatedAt)
    {
        $userEmailHash  = AuthEncryptHelper::encode($userEmail, $userCreatedAt, 255);
        $userIdHash     = AuthEncryptHelper::encode($userId, $userEmailHash, 64);

        return [$userIdHash, $userEmailHash];
    }

    public function switchIdentity($identity, $duration = 0)
    {
        $this->setIdentity($identity);
        if ($identity === null) {
            AuthCookieHelper::removeAuthCookie();
        }
    }

    public function getIsGuest()
    {
        return $this->getIdentity() === null;
    }
}
