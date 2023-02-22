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
    'defaultRoute' => 'chat/index',
    'language' => 'ru-RU',
    'name' => 'Смайл Чат',
    'components' => [
        'request' => [
            'class' => \common\ext\web\Request::class,
        ],
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
                    'exportInterval' => 1,
                    'logVars' => [],
                ],
            ],
            'flushInterval' => 1,
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
