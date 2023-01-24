<?php
namespace frontend\assets;

use yii\web\AssetBundle;

class MessagesAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/loader.css',
    ];

    public $js = [
        'js/ChatPanel.js',
        'js/ChatLoadPager.js',
        'js/NewMsgForm.js',
    ];

    public $depends = [
        'yii\jui\JuiAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}