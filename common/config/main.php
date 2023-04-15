<?php

function print_r2($obj, $text = '')
{
    echo "$text<pre>";
    print_r($obj);
    echo "</pre>";
}

$redisConfigs = [
    'class' => 'yii\redis\Connection',
    'hostname' => '172.18.0.103',
    'port' => 6379,
];

return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'timeZone' => 'Europe/Kyiv',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
//        'cache' => [
//            'class' => 'yii\caching\MemCache',
//            'servers' => [
//                [
//                    'host' => '172.18.0.104',
//                    'port' => 11211,
//                    'weight' => 100,
//                ],
//            ],
//            'useMemcached' => true,
//        ],
        // sessions
        'redisDb1' => array_merge(['database' => 1], $redisConfigs),
        // user auth
        'redisDb2' => array_merge(['database' => 2], $redisConfigs),
        // chat messages
        'redisDb3' => array_merge(['database' => 3], $redisConfigs),
        // "отложенные сообщения"
        'redisDb5' => array_merge(['database' => 5], $redisConfigs),
    ],
];
