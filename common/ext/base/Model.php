<?php

namespace common\ext\base;

use common\ext\patterns\Singleton;

class Model extends \yii\base\Model
{
    use Singleton;

    public $model;
}
