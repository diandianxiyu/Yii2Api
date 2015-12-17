<?php

/**
 * 
 * 稿件相关的接口
 * 
 * 
 * 2015-09-14 15:05:33
 * Calvin
 * 
 * 
 */

namespace app\patch\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\helper\RsaDecode; //加密相关
use app\components\helper\Validators; //验证相关
use app\components\error\Error; //错误返回
use app\models\TokenUser;
use app\models\ArticlePhrase;
use app\models\CensusApi;
use app\models\ArticleBase;
use app\models\UserStatus;
use app\models\UserAccount;


class ArticlesController extends Controller {

    //定义本类的名称
    private $modules_name = 'v1';
    private $class_name = 'articles';
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
                    'phase' => ['get'],
                    'timeline' => ['get'],
                    'info' => ['get'],
                ],
            ],
        ];
    }

    /**
     * 获取稿件的列表
     */
    public function actionTimeline() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.0";
        //获取参数,和其他的分页的一样
        $param_os = (int) $request->get("os");   //渠道
        $param_timelimit = $request->get("timelimit") ? $request->get("timelimit") : time();   //访问第一页的时候的时间戳
        $param_count = $request->get("count") ? $request->get("count") : 10;   //每页返回的数量，默认10
        $param_page = $request->get("page") ? $request->get("page") : 0;   //页码，默认第一页
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->get("token");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //解密token
        if ($header_token == '233' && (YII_ENV_LOCAL == 'local' || YII_ENV_DEV )) {
            $uid = 1024;
        } else {
            $token_decode = RsaDecode::clientTokenToArray($header_token);
            if ($token_decode === FALSE) {
                //返回错误信息
                return $response->data = Error::errorJson($this_path, 9003);
            }
            //解密
            //验证token
            $check_user_token = TokenUser::check($token_decode['uid'], $token_decode['deviceid'], $token_decode['logintime']);
            if ($check_user_token !== "0") {
                return $response->data = Error::errorJson($this_path, $check_user_token);
            }
            $uid = $token_decode['uid'];
        }

        $user_id = UserAccount::getUserId($uid);
        //进行数据的获取
        //获取置顶
        $top_articles = ArticleBase::getTop($param_os,$uid);
        $list = ArticleBase::getArticleList($param_timelimit, $param_count, $param_page, $param_os,$uid);
        $next_page = ArticleBase::articleListNextPage($param_timelimit, $param_count, $param_page) ? 1 : 0;
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'top' => $top_articles,
                'list' => $list,
                'next_page' => $next_page,
            ],
            'version' => $this_allow_version,
            'error_code' => 0,
            'error' => "",
        ];
        //接口访问记录
        CensusApi::add($user_id, $this_path);
        return $response->data = $return_json;
    }

    /**
     * 获取稿件的详情
     */
    public function actionInfo() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.0";
        //获取参数,和其他的分页的一样
        $param_os = (int) $request->get("os");   //渠道
        $param_tid = $request->get("tid") ? $request->get("tid") : 0;   //稿件的id
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->get("token");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token, $param_tid]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //解密token
        if ($header_token == '233' && (YII_ENV_LOCAL == 'local' || YII_ENV_DEV )) {
            $uid = 1024;
        } else {
            $token_decode = RsaDecode::clientTokenToArray($header_token);
            if ($token_decode === FALSE) {
                //返回错误信息
                return $response->data = Error::errorJson($this_path, 9003);
            }
            //解密
            //验证token
            $check_user_token = TokenUser::check($token_decode['uid'], $token_decode['deviceid'], $token_decode['logintime']);
            if ($check_user_token !== "0") {
                return $response->data = Error::errorJson($this_path, $check_user_token);
            }
            $uid = $token_decode['uid'];
        }


        //判断稿件是不是已经存在
        if (ArticleBase::checkExist($param_tid) == FALSE) {
            return $response->data = Error::errorJson($this_path, 2001);
        }

        //根据uid获取user_id
        $user_id = UserAccount::getUserId($uid);
        //进行数据的获取 
        $article_info = ArticleBase::getInfo($param_tid, $param_os,$uid);
//        $related_info = ArticleBase::getRelated($param_tid, $param_os);
        $check = UserStatus::checkCommentStatus($user_id) ? 1 : 0;
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'article' => $article_info,
                //'related' => $related_info,
                'allow_comment' => $check
            ],
            'version' => $this_allow_version,
            'error_code' => 0,
            'error' => "",
        ];
        //接口访问记录
        CensusApi::add($user_id, $this_path);
        return $response->data = $return_json;
    }

    /**
     * 获取快捷编辑的短语
     */
    public function actionPhrases() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.0";
        //获取参数
        $param_os = (int) $request->get("os");   //渠道
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->get("token");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //测试环境本地环境对 token 不做验证
        if ($header_token == '233' && (YII_ENV_LOCAL == 'local' || YII_ENV_DEV )) {
            $uid = 1024;
        } else {
            $token_decode = RsaDecode::clientTokenToArray($header_token);
            if ($token_decode === FALSE) {
                //返回错误信息
                return $response->data = Error::errorJson($this_path, 9003);
            }
            //解密
            //验证token
            $check_user_token = TokenUser::check($token_decode['uid'], $token_decode['deviceid'], $token_decode['logintime']);
            if ($check_user_token !== "0") {
                return $response->data = Error::errorJson($this_path, $check_user_token);
            }
            $uid = $token_decode['uid'];
        }
        
        
        $user_id = UserAccount::getUserId($uid);

        //返回结果
        $phrases = ArticlePhrase::getAll();
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => $phrases,
            'version' => $this_allow_version,
            'error_code' => 0,
            'error' => "",
        ];
        //接口访问记录
        CensusApi::add($user_id, $this_path);
        return $response->data = $return_json;
    }

}
