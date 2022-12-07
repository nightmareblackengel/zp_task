<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
        ],
//        'cache' => [
//            'class' => 'yii\caching\MemCache',
//            'servers' => [
//                [
//                    'host' => '127.0.0.1',
//                    'port' => 11211,
//                    'weight' => 100,
//                ],
//            ],
//            'useMemcached' => true,
//        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => 'redis1',
        ],
        'redis1' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '172.18.0.103',
            'port' => 6379,
            'database' => 1,
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
            'class' => 'yii\redis\Session',
            'redis' => 'redis1',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [

            ],
        ],
    ],
    'params' => $params,
];
