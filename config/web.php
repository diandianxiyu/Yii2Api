<?php

if (YII_ENV_LOCAL == 'local') {
    $params_file = 'params_local';
} else if (YII_ENV_DEV) {
    $params_file = 'params_dev';
} else {
    $params_file = 'params_prod';
}

$params = require(__DIR__ . "/{$params_file}.php");

$config = [
    'id' => 'pro-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '21312',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        ],
        'db' => require(__DIR__ . '/db_prod_app.php'),
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\patch\v1\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV || YII_ENV_LOCAL == 'local') {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
    $config['components']['db'] = require(__DIR__ . '/db_dev_app.php');
}

return $config;
