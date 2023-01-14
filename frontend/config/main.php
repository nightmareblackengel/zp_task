<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$redisConfigs = [
    'class' => 'yii\redis\Connection',
    'hostname' => '172.18.0.103',
    'port' => 6379,
];

return [
    'id' => 'ztt',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'defaultRoute' => 'chat/index',
    'language' => 'ru-RU',
    'name' => 'Смайл Чат',
    'components' => [
        'request' => [
            'class' => \common\ext\web\Request::class,
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
        // sessions
        'redisDb1' => array_merge(['database' => 1], $redisConfigs),
        // user auth
        'redisDb2' => array_merge(['database' => 2], $redisConfigs),
        // chat messages
        'redisDb3' => array_merge(['database' => 3], $redisConfigs),
        'user' => [
            'class' => \frontend\models\UserAuth::class,
        ],
        'session' => [
            'name' => 'ztt-session',
            'class' => \frontend\models\RedisSession::class,
            'redis' => 'redisDb1',
            'keyPrefix' => 'sess_',
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
            'errorAction' => 'main/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'chat/index/<chat_id>' => 'chat/index',
            ],
        ],
    ],
    'params' => $params,
];
