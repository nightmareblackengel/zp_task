<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'ztt',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-ztt',
        ],
        'cache' => [
            'class' => 'yii\caching\MemCache',
            'servers' => [
                [
                    'host' => '172.18.0.104',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ],
            'useMemcached' => true,
        ],
//        'cache' => [
//            'class' => 'yii\redis\Cache',
//            'redis' => 'redis1',
//        ],
        'redisDb1' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '172.18.0.103',
            'port' => 6379,
            'database' => 1,
        ],
        'user' => [
            'class' => \frontend\models\UserAuth::class,
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-frontend',
            'class' => 'yii\redis\Session',
            'redis' => 'redisDb1',
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
