<?php
namespace frontend\ext\helpers;

use Yii;
use yii\web\Cookie;

class AuthCookieHelper
{
    public const COOK_AUTH = 'ztt-auth';

    public const AUTH_TIMEOUT = 60 * 5;

    public static function getAuthCookie()
    {
        $cookieCollection = Yii::$app->request->getCookies();
        if (empty($cookieCollection)) {
            return null;
        }
        return $cookieCollection->get(self::COOK_AUTH);
    }

    public static function sendCookie(string $redisKey, int $duration, array $cookieParams = [])
    {
        $cookieParam = array_merge(
            $cookieParams,
            [
                'value' => $redisKey,
                'expire' => time() + $duration,
            ]
        );

        $cookies = Yii::$app->response->cookies;
        $cookies->add(
            new Cookie($cookieParam)
        );
    }

    public static function removeAuthCookie($retRes = null)
    {
        $cookieCollection = Yii::$app->request->getCookies();
        $cookieCollection->readOnly = false;
        $cookieCollection->remove(self::COOK_AUTH);

        return $retRes;
    }
}
