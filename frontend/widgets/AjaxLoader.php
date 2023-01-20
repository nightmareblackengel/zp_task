<?php
namespace frontend\widgets;

use common\ext\helpers\Html;
use Yii;
use yii\base\Widget;

class AjaxLoader extends Widget
{
    public function init()
    {
        parent::init();
    }

    public function run()
    {
        return '';
    }

    public static function begin($config = [])
    {
        return Yii::$app->view->render('@frontend/views/widgets/ajax-loader/begin', [
            'code' => $config['code'] ?? '',
        ]);
    }

    public static function end()
    {
        return Html::endTag('div') . Html::endTag('div');
    }
}
