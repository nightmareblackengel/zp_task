<?php
namespace common\ext\base;

use common\ext\traits\ErrorTrait;

class Form extends \yii\base\Model
{
    use ErrorTrait;

    const DEFAULT_ERR_ATTRIBUTE = '-';

    public function getDefaultError()
    {
        if (empty($this->_errors[self::DEFAULT_ERR_ATTRIBUTE])) {
            return '';
        }

        return array_shift($this->_errors[self::DEFAULT_ERR_ATTRIBUTE]);
    }
}
