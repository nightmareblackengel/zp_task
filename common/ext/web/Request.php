<?php
namespace common\ext\web;

use Yii;

class Request extends \yii\web\Request
{
    protected $_csrfToken;
    public $csrfParam = 'ztt-random-passphrase';

    public function getCsrfToken($regenerate = false)
    {
        if ($this->_csrfToken === null || $regenerate) {
            $token = $this->loadCsrfToken();

            if ($regenerate || empty($token)) {
                $token = $this->generateCsrfToken();
            }

            $this->_csrfToken = $token;
        }

        return $this->_csrfToken;
    }

    protected function generateCsrfToken()
    {
        $token = Yii::$app->getSecurity()->generateRandomString(1024);
        if ($this->enableCsrfCookie) {
            $cookie = $this->createCsrfCookie($token);
            Yii::$app->getResponse()->getCookies()->add($cookie);
        } else {
            Yii::$app->getSession()->set($this->csrfParam, $token);
        }

        return $token;
    }

//    protected function createCsrfCookie($token)
//    {
//        $options = $this->csrfCookie;
//        return Yii::createObject(array_merge($options, [
//            'class' => 'yii\web\Cookie',
//            'name' => $this->csrfParam,
//            'value' => $token,
//        ]));
//    }

    /****************************************************************************/
    /** Замена всех приватных методов на "защищенные", чтобы можно было пользоваться переменными **/
    /****************************************************************************/
//    protected $_cookies;
//    protected $_headers;
//
//    protected $_bodyParams;
//    protected $_rawBody;
//    protected $_queryParams;
//    protected $_hostInfo;
//    protected $_hostName;
//    protected $_baseUrl;
//    protected $_scriptUrl;
//    protected $_scriptFile;
//    protected $_pathInfo;
//    protected $_url;
//    protected $_port;
//    protected $_securePort;
//    protected $_contentTypes;
//    protected $_languages;
//    protected $_secureForwardedHeaderParts;
//
//    protected function validateCsrfTokenInternal($clientSuppliedToken, $trueToken)
//    {
//        if (!is_string($clientSuppliedToken)) {
//            return false;
//        }
//
//        $security = Yii::$app->security;
//
//        return $security->compareString($security->unmaskToken($clientSuppliedToken), $security->unmaskToken($trueToken));
//    }
//
//    protected function utf8Encode($s)
//    {
//        $s .= $s;
//        $len = \strlen($s);
//        for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
//            switch (true) {
//                case $s[$i] < "\x80": $s[$j] = $s[$i]; break;
//                case $s[$i] < "\xC0": $s[$j] = "\xC2"; $s[++$j] = $s[$i]; break;
//                default: $s[$j] = "\xC3"; $s[++$j] = \chr(\ord($s[$i]) - 64); break;
//            }
//        }
//        return substr($s, 0, $j);
//    }
}
