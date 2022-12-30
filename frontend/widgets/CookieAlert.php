<?php

namespace frontend\widgets;

use common\models\redis\FlashHashesStorage;
use Yii;
use yii\bootstrap\Widget;
use yii\web\Cookie;
use yii\web\CookieCollection;

class CookieAlert extends Widget
{
    const COOKIE_NAME = 'alert';

    public static function addMessage($message, $key = null)
    {
        $newKey = $key ?? rand(1, 9999);

        Yii::$app->response->cookies->add(new Cookie([
            'name' => static::COOKIE_NAME,
            'value' => [
                $newKey => $message,
            ],
        ]));
    }

    public function run(): string
    {
        /** @var CookieCollection $cookieCollection */
        $cookieCollection = Yii::$app->request->getCookies();
        $alertCookie = $cookieCollection->get(static::COOKIE_NAME);

        if (empty($alertCookie)) {
            return $this->removeCookie();
        }
        if (empty($alertCookie->value)) {
            $this->removeCookie();
            return $this->removeCookie();
        }
        $message = array_shift($alertCookie->value);
        if (empty($message)) {
            return $this->removeCookie();
        }
        $this->removeCookie();

        return $this->render('@frontend/views/widgets/cookie-alert', [
            'message' => $message,
        ]);
    }

    public function removeCookie($return = '')
    {
        Yii::$app->response->cookies->remove(self::COOKIE_NAME);
        return $return;
    }
}
