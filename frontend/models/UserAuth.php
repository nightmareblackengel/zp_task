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

    public $identityClass = UserAuthIdentity::class;

    public $enableAutoLogin = true;

    public $identityCookie = ['name' => '_identity-ztt', 'httpOnly' => true,];

    public $authTimeout = 60 * 5;

    protected $userIdentity = null;

    public function loginByAccessToken($token, $type = null)
    {
        echo 'loginByAccessToken<pre>';
        debug_print_backtrace();
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
            return $this->removeAuthCookie($cookieCollection);
        }

        $redisValue = Yii::$app->redisDb2->get($authCook->value);
        if (empty($redisValue)) {
            return $this->removeAuthCookie($cookieCollection);
        }

        $redisAuthData = unserialize($redisValue);
        if (empty($redisAuthData['auth'])) {
            return $this->removeAuthCookie($cookieCollection);
        }

        $userId = EncryptHelper::decode($authCook->value, $redisAuthData['auth'], 31);
        if (empty($userId)) {
            return $this->removeAuthCookie($cookieCollection);
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
        $userEmailHash  = EncryptHelper::encode($userData['email'], $userData['created_at'], 255);
        $userIdHash     = EncryptHelper::encode($userData['id'], $userEmailHash, 64);

        $redisKey = $userIdHash;
        $resisHashData = serialize([
            'auth' => $userEmailHash,
//            'ip' => '',
//            'browserId' => '',
            'session' => Yii::$app->session->getId(),
        ]);
        if (empty(Yii::$app->redisDb2->setex($redisKey, $this->authTimeout, $resisHashData))) {
            return null;
        }

        $cookies = Yii::$app->response->cookies;
        $cookies->add(new Cookie([
            'name' => self::COOK_AUTH,
            'value' => $redisKey,
            'expire' => time() + $this->authTimeout,
        ]));

        return true;
    }

    protected function removeAuthCookie(CookieCollection $cookieCollection, $retRes = null)
    {
        $cookieCollection->readOnly = false;
        $cookieCollection->remove(self::COOK_AUTH);

        return $retRes;
    }
}
