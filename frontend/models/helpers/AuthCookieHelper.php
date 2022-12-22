<?php
namespace frontend\models\helpers;

use Yii;
use yii\web\Cookie;

class AuthCookieHelper
{
    public const COOK_AUTH = 'ztt-csrf';

    public const AUTH_TIMEOUT = 60 * 60;

    public static function getAuthCookie()
    {
        $cookieCollection = Yii::$app->request->getCookies();
        if (empty($cookieCollection)) {
            return null;
        }
        return $cookieCollection->get(self::COOK_AUTH);
    }

    public static function sendCookie(string $value, int $duration, array $cookieParams = [])
    {
        $cookieParam = array_merge(
            $cookieParams,
            [
                'value' => $value,
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
        $cookieCollection = Yii::$app->response->getCookies();
        $cookieCollection->readOnly = false;
        $cookieCollection->remove(self::COOK_AUTH);

        return $retRes;
    }
}
