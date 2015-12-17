<?php

if (YII_ENV_LOCAL == 'local') {
    //本地
    return [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=xxx',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
        'tablePrefix' => '',
    ];
} else {
    //线上测试
    return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=xxxxxxx;dbname=xxxxx',
    'username' => 'xxxxx',
    'password' => 'xxxxx',
    'charset' => 'utf8mb4',
    'tablePrefix'=>'',
   
    ];
}