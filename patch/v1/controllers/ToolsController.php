<?php

namespace app\patch\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\helper\RsaDecode; //加密相关
use app\components\helper\Validators; //验证相关
use app\components\error\Error; //错误返回
use app\models\UserAccount;
use app\models\TokenUser;
use app\models\CensusApi;
use app\models\AppVersion;
use app\models\CensusShare;
use app\models\ArticleBase;
use app\models\SwitchIos;
use app\models\CensusItemShare;
use app\models\ArticleItem;

class ToolsController extends Controller {

//定义本类的名称
    private $modules_name = 'v1';
    private $class_name = 'tools';
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
                    'share' => ['post'],
                    'item-share' => ['post'],
                    'version' => ['get'],
                    'iso-online' => ['get']
                ],
            ],
        ];
    }

    /**
     * 获取最新的版本
     */
    public function actionVersion() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.0,1.1";
//获取参数
        $param_os = (int) $request->get("os");   //设备对应的
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

//获取最新的数据
        $version = AppVersion::getActiveVersion($param_os);
        $version['timestamp'] = time();
//返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => $version,
            'version' => $this_allow_version,
            'error_code' => 0,
            'error' => "",
        ];
        CensusApi::add($user_id, $this_path);
        return $response->data = $return_json;
    }

    /**
     * 渠道分享
     */
    public function actionShare() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.0,11.1";
//获取参数,和其他的分页的一样
        $param_os = (int) $request->post("os");   //操作系统
        $param_tid = $request->post("tid");    //稿件的id
        $param_share = $request->post("share"); //分享的方式 朋友圈1，微信好友2，QQ空间3，QQ好友4，新浪微博5
//获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->post("token");    //加密之后的设备号，做到兼容post的方式
//验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token, $param_tid, $param_share]);
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

        if ($param_tid != 0) {
//判断稿件是不是已经存在
            if (ArticleBase::checkExist($param_tid) == FALSE) {
                return $response->data = Error::errorJson($this_path, 2001);
            }
        }


//写入分享记录
        CensusShare::addRecord($user_id, $param_share);
//返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'tid' => (int) $param_tid,
                'share' => (int) $param_share,
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
     * 渠道分享
     */
    public function actionItemShare() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
//获取参数,和其他的分页的一样
        $param_os = (int) $request->post("os");   //操作系统
        $param_iid = $request->post("iid");    
        $param_share = $request->post("share"); //分享的方式 朋友圈1，微信好友2，QQ空间3，QQ好友4，新浪微博5
//获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->post("token");    //加密之后的设备号，做到兼容post的方式
//验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token, $param_iid, $param_share]);
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

        if ($param_iid != 0) {

            if (ArticleItem::checkExist($param_iid) == FALSE) {
                return $response->data = Error::errorJson($this_path, 2001);
            }
        }

        CensusItemShare::addRecord($param_iid, $param_share);

        $return_json = [
            'request' => $this_path,
            'info' => [
                'tid' => (int) $param_iid,
                'share' => (int) $param_share,
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
     * iOS线上环境切换
     * */
    public function actionIosOnline() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
//获取最新的
        $param_name = $request->get("name") ? $request->get("name") : "1.0";
//0审核 1上线
        $info = SwitchIos::find()->where(['name' => $param_name, 'status' => 1])->one();

        if ($info) {
            unset($info['id']);
            unset($info['status']);
        } else {
            $info = [
                'name' => $param_name,
                'online_status' => 0,
            ];
        }


//返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => $info,
            'version' => $this_allow_version,
            'error_code' => 0,
            'error' => "",
        ];
        return $response->data = $return_json;
    }

}
