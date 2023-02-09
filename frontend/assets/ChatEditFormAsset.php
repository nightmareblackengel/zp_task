<?php

namespace frontend\assets;

use yii\web\AssetBundle;

class ChatEditFormAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/ChatEditForm.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
