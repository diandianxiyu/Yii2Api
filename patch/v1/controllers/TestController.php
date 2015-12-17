<?php

namespace app\patch\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\CensusApp;

class TestController extends Controller {

    //定义本类的名称
    private $modules_name = 'v1';
    private $class_name = 'test';
    public $enableCsrfValidation = false;

    /**
     * @inheritdoc  相关的操作
     */
    public function behaviors() {
        return [
            //控制访问规范
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                   
                ],
            ],
        ];
    }
    
    /**
     * 增加活跃用户
     */
    public function actionTest(){
//       $a= CensusApp::addUser();
//       var_dump($a);
    }
}