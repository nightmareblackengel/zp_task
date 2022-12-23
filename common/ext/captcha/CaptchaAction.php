<?php
namespace common\ext\captcha;

class CaptchaAction extends \yii\captcha\CaptchaAction
{
    public $testLimit = 1;
    public $width = 300;
    public $height = 100;
    public $padding = 5;
    public $transparent = true;
    public $offset = -1;
    public $fontFile = '@common/ext/captcha/backend.otf';

    public function __construct($id, $controller, array $config = []) {
        parent::__construct($id, $controller, $config);
        $this->foreColor = rand(0, 16777215);
        $this->backColor = rand(0, 16777215);
        $this->transparent = true;
    }

    protected function generateVerifyCode() {
        // https://www.wfonts.com/font/captcha-code
        $symbols = 'TQWYUIOPASDFGHJKLZXCVBN2345678=()[]'; // ЙЦЯЁБШДФЖЧЮ  1ETRM@#$%&()9
        $symbLen = strlen($symbols) - 1;

        $textLenght = 1; //rand(7, 10);
        $text = '';

        for($i = 0; $i < $textLenght; $i++) {
            $text .= $symbols[rand(0, $symbLen)];
        }

        return $text;
    }
}
