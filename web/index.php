<?php

if($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == 'www.api.com' ){
    //表示本地测试
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV_LOCAL') or define('YII_ENV_LOCAL', 'local');
}else

if(count(explode('-1.wx.jaeapp.com', $_SERVER['SERVER_NAME'])) == 2|| $_SERVER['SERVER_NAME'] == 'gmapitest2015.ipicopico.com'){
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV') or define('YII_ENV', 'dev');
    defined('YII_ENV_LOCAL') or define('YII_ENV_LOCAL', 'test');
}
else{
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV_LOCAL') or define('YII_ENV_LOCAL', 'prod');
}

// var_dump($_REQUEST);
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');


(new yii\web\Application($config))->run();
