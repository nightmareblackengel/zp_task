<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class ChatFormAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/ChatForm.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
