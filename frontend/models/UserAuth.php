<?php
namespace frontend\models;

use common\ext\helpers\EncryptHelper;
use Yii;
use yii\base\InvalidValueException;
use yii\web\Cookie;
use yii\web\CookieCollection;
use yii\web\IdentityInterface;

class UserAuth extends \yii\web\User
{
    public const COOK_AUTH = 'ztt-auth';
    public const AUTH_TIMEOUT = 60 * 5;

    public $identityClass = UserAuthIdentity::class;

    public $enableAutoLogin = true;

    public $identityCookie = ['name' => self::COOK_AUTH, 'httpOnly' => true,];

    public $authTimeout = self::AUTH_TIMEOUT;

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

        $cookieCollection = Yii::$app->request->getCookies();
        if (empty($cookieCollection)) {
            return null;
        }
        $authCook = $cookieCollection->get(self::COOK_AUTH);
        if (empty($authCook->value)) {
            return $this->removeAuthCookie();
        }

        $redisValue = Yii::$app->redisDb2->get($authCook->value);
        if (empty($redisValue)) {
            return $this->removeAuthCookie();
        }

        $redisAuthData = unserialize($redisValue);
        if (empty($redisAuthData['auth'])) {
            return $this->removeAuthCookie();
        }

        $userId = EncryptHelper::decode($authCook->value, $redisAuthData['auth'], 31);
        if (empty($userId)) {
            return $this->removeAuthCookie();
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

        $this->sendCookie($redisKey, $this->authTimeout);
        $this->userIdentity = $this->userIdentity;

        return true;
    }

    protected function generateUserHashes($userId, ?string $userEmail, ?string $userCreatedAt)
    {
        $userEmailHash  = EncryptHelper::encode($userEmail, $userCreatedAt, 255);
        $userIdHash     = EncryptHelper::encode($userId, $userEmailHash, 64);

        return [$userIdHash, $userEmailHash];
    }

    protected function removeAuthCookie($retRes = null)
    {
        $cookieCollection = Yii::$app->request->getCookies();
        $cookieCollection->readOnly = false;
        $cookieCollection->remove(self::COOK_AUTH);

        return $retRes;
    }

    protected function sendCookie(string $redisKey, int $duration)
    {
        $cookieParam = array_merge(
            $this->identityCookie,
            [
                'value' => $redisKey,
                'expire' => time() + $this->authTimeout,
            ]
        );

        $cookies = Yii::$app->response->cookies;
        $cookies->add(
            new Cookie($cookieParam)
        );
    }

    public function switchIdentity($identity, $duration = 0)
    {
        $this->setIdentity($identity);
        if ($identity === null) {
            $this->removeAuthCookie();
        }
    }

    public function getIsGuest()
    {
        return $this->getIdentity() === null;
    }
}
