<?php
namespace common\ext\web;

use Yii;

class Application extends \yii\web\Application
{
    public function run()
    {
        register_shutdown_function([static::class, 'shutDownHadler']);

        return parent::run();
    }

    public static function shutDownHadler()
    {

    }
}
