<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                  
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
//        if (YII_ENV_DEV || YII_ENV_LOCAL) {
          //  echo "我是开发环境 dev";
//        }else{
        //    echo "我是生产环境 prod";
//        }
      //  echo "<br>";
      // echo  Rsa::privEncrypt('233');
        
        
        //测试代码检查是否为线上环境
//        $http=;
//        $count=  count(explode('-1.wx.jaeapp.com', $_SERVER['SERVER_NAME']));
//        var_dump($count);
//        return $this->render('index');
    }
}
