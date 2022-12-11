<?php

namespace common\ext;

use common\ext\patterns\Singleton;

class Model extends \yii\base\Model
{
    use Singleton;

    public $model;
}
