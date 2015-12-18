<?php

/**
 * 
 * 账户相关接口
 * 
 * 更新 
 *  2015-09-11 15:20:04  Calvin 创建
 *  2015-10-10 15:51:09   v1.1版本接口完成
 *  2015-12-17 16:51:48   迁移环境做本地测试
 * 
 */

namespace app\patch\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\helper\RsaDecode; //加密相关
use app\components\helper\Validators; //验证相关
use app\components\error\Error; //错误返回
use app\models\UserEmblem;
use app\models\NumberOrder;
use app\models\UserAccount;
use app\models\UserAvatar;
use app\models\TokenUser;
use app\models\CensusApi;
use app\models\UserPassword;
use app\components\helper\WebImgToLocal;
use app\models\UserProfile;
use app\components\helper\App;
use app\models\CensusApp;

class AccountsController extends Controller {

    //定义本类的名称
    private $modules_name = 'v1';
    private $class_name = 'accounts';
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
                    'login' => ['post'],
                    'mob-login' => ['post'],
                    'mob-reg' => ['post'],
                    'tir-login'=>['post'],
                    'forget-login'=>['post'],
                    'edit-login'=>['post'],
                    'check-mobile'=>['post'],
                ],
            ],
        ];
    }

    /**
     * 用户采用设备号进行登录
     */
    public function actionLogin() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.0";
        //获取参数
        $param_deviceid = $request->post("deviceid");   //设备id
        $param_clienttime = (int) $request->post("clienttime");   //客户端时间
        $param_channel = $request->post("channel");   //渠道
        $param_os = (int) $request->post("os");   //渠道
        //获取token
        $headers = $request->headers;
        $header_openid = $headers->get('Openid') ? $headers->get('Openid') : $request->post("openid");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_deviceid, $param_clienttime, $header_openid, $param_channel, $param_os]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //验证加密
        $decode_hear_openid = RsaDecode::rsa_decode($header_openid);
        if ($decode_hear_openid !== $param_deviceid) {
            return $response->data = Error::errorJson($this_path, 9001);
        }//验证通过，证明了合法性
        //验证这个设备是不是进行过注册的操作
        $user_id = UserEmblem::checkExist($param_deviceid);
        if ($user_id == 0) {
            //新的设备，进行注册操作
            // 获取新的uid,写入 用户基础表，写入 用户标识表 ，写入 用户头像表
            $uid = NumberOrder::getNewid(1);
            $user_id = UserAccount::add($uid, $param_channel, $param_os);
            UserAvatar::add($user_id, Yii::$app->params['default_avatar']);
            UserEmblem::add($user_id, $param_deviceid);
            UserProfile::add($user_id, "游客".  rand(1000, 9999), 1);
            
        } else {
            $uid = UserAccount::getUid($user_id);
        }

        //写入token
        TokenUser::joinToken($uid, $param_deviceid, $param_clienttime, time(), time());
        //获取用户的基本信息 uid，头像，那么直接返回用户的头像
        $user_avatar = UserAvatar::get($user_id);
        $user_info = [
            'uid' => $uid,
            'avatar' => $user_avatar,
        ];
        //返回对应的token
        $token = RsaDecode::tokenMake($uid);
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'token' => $token,
                'user_info' => $user_info,
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
     * 手机号注册
     */
    public function actionMobReg() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数
        $param_deviceid = $request->post("deviceid");   //设备id
        $param_clienttime = (int) $request->post("clienttime");   //客户端时间
        $param_channel = $request->post("channel");   //渠道
        $param_os = (int) $request->post("os");   //设备终端
        $param_mobile = $request->post("mobile");   //设备终端
        $param_pwd = $request->post("pwd");   //设备终端
        $param_gender = (int)$request->post("gender");   //设备终端
        $param_nikename = $request->post("nikename");   //设备终端
        $param_avatar = isset($_FILES['avatar']['error']);   //头像上传，如果值是 true 再看 error 是不是0，如果是0就表示有对应的头像上传
        //获取token
        $headers = $request->headers;
        $header_openid = $headers->get('Openid') ? $headers->get('Openid') : $request->post("openid");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_nikename,$param_deviceid, $param_clienttime, $header_openid, $param_channel, $param_os, $param_mobile, $param_pwd, $param_gender]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //验证加密
        $decode_hear_openid = RsaDecode::rsa_decode($header_openid);
        if ($decode_hear_openid !== $param_deviceid) {
            return $response->data = Error::errorJson($this_path, 9001);
        }//验证通过，证明了合法性   
        
        //验证性别
        if(!in_array($param_gender, [1,2])){
            //性别输入不符
            return $response->data = Error::errorJson($this_path, 9001);
        }
        //验证手机号是不是进行过注册的操作
        $user_id = UserEmblem::checkExist($param_mobile, 2);
        if ($user_id == 0) {
            //新的手机号，进行注册操作，【 用户账户，用户标识，用户头像，用户基础信息 】
            // 获取新的uid,写入 用户基础表，写入 用户标识表 ，写入 用户头像表
            $uid = NumberOrder::getNewid(1);
            $user_id = UserAccount::add($uid, $param_channel, $param_os);
            //用户相关的标识
            UserEmblem::add($user_id, $param_mobile, 2);
            //密码
            UserPassword::add($user_id, RsaDecode::rsa_decode($param_pwd));
            //获取用户的头像
            if ($param_avatar == true && $_FILES['avatar']['error'] == 0) {
                //获取对应的
                $avatar_url = WebImgToLocal::simpleUploadV2($_FILES['avatar']);
            } else {
                $avatar_url = Yii::$app->params['default_avatar'];
            }
            UserAvatar::add($user_id, $avatar_url);
            //用户的基本信息
            UserProfile::add($user_id, $param_nikename, $param_gender);
            //新增用户的统计
            CensusApp::addUser();
        } else {
            //返回错误，手机号已经被注册 4001
            return $response->data = Error::errorJson($this_path, 4001);
        }

        //写入token
        TokenUser::joinToken($uid, $param_deviceid, $param_clienttime, time(), time());
        //返回对应的token
        $token = RsaDecode::tokenMake($uid);
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'token' => $token,
                'user_info' => App::getUserProfile($user_id),
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
     * 手机号登录
     */
    public function actionMobLogin() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数
        $param_deviceid = $request->post("deviceid");   //设备id
        $param_clienttime = (int) $request->post("clienttime");   //客户端时间
        $param_os = (int) $request->post("os");   //设备终端
        $param_mobile = $request->post("mobile");   //手机号
        $param_pwd = $request->post("pwd");   //密码
        //获取token
        $headers = $request->headers;
        $header_openid = $headers->get('Openid') ? $headers->get('Openid') : $request->post("openid");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_deviceid, $param_clienttime, $header_openid, $param_os, $param_mobile, $param_pwd]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //验证加密
        $decode_hear_openid = RsaDecode::rsa_decode($header_openid);
        if ($decode_hear_openid !== $param_deviceid) {
            return $response->data = Error::errorJson($this_path, 9001);
        }//验证通过，证明了合法性      
        //验证手机号是不是进行过注册的操作
        $user_id = UserEmblem::checkExist($param_mobile, 2);
        if ($user_id == 0) {
            //返回错误，手机号或密码错误 4002
            return $response->data = Error::errorJson($this_path, 4002);
        }
        $check_pwd = UserPassword::check($user_id, RsaDecode::rsa_decode($param_pwd));

        if ($check_pwd == FALSE) {
            //返回错误，手机号或密码错误 4002
            return $response->data = Error::errorJson($this_path, 4002);
        }
        $uid = UserAccount::getUid($user_id);
        //写入token
        TokenUser::joinToken($uid, $param_deviceid, $param_clienttime, time(), time());
        //返回对应的token
        $token = RsaDecode::tokenMake($uid);
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'token' => $token,
                'user_info' => App::getUserProfile($user_id),
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
     * 第三方账户登录
     */
    public function actionTriLogin() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数
        $param_deviceid = $request->post("deviceid");   //设备id
        $param_clienttime = (int) $request->post("clienttime");   //客户端时间
        $param_os = (int) $request->post("os");   //设备终端
        $param_channel = $request->post("channel");   //渠道
        $param_emblem = $request->post("emblem");   //第三方的标识
        $param_type = (int) $request->post("type");   //登录类型
        $param_gender = (int) $request->post("gender");   //性别
        $param_nikename = $request->post("nikename");   //昵称
        $param_avatar = $request->post("avatar");   //头像地址
        //获取token
        $headers = $request->headers;
        $header_openid = $headers->get('Openid') ? $headers->get('Openid') : $request->post("openid");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_deviceid, $param_channel, $param_clienttime, $header_openid, $param_os, $param_emblem, $param_type, $param_gender, $param_nikename, $param_avatar]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //验证加密
        $decode_hear_openid = RsaDecode::rsa_decode($header_openid);
        if ($decode_hear_openid !== $param_deviceid) {
            return $response->data = Error::errorJson($this_path, 9001);
        }//验证通过，证明了合法性

        //验证性别
        if(!in_array($param_gender, [1,2,3])){
            //性别输入不符
            return $response->data = Error::errorJson($this_path, 9001);
        }
        //验证登录方式
        $login_type = $param_type + 2;
        if ($login_type < 3 || $login_type > 7) {
            //返回错误信息，错误的登录方式
            return $response->data = Error::errorJson($this_path, 4003);
        }

        //验证手机号是不是进行过注册的操作
        $user_id = UserEmblem::checkExist($param_emblem, $login_type);
        if ($user_id == 0) {
            //注册新用户
            // 获取新的uid,写入 用户基础表，写入 用户标识表 ，写入 用户头像表
            $uid = NumberOrder::getNewid(1);
            $user_id = UserAccount::add($uid, $param_channel, $param_os);
            //用户相关的标识
            UserEmblem::add($user_id, $param_emblem, $login_type);
            //获取用户的头像
            $avatar_url = WebImgToLocal::web2oss($param_avatar);
            UserAvatar::add($user_id, $avatar_url);
            //用户的基本信息
            UserProfile::add($user_id, $param_nikename, $param_gender);
            //新的
            $login_type = 1;
             //新增用户的统计
            CensusApp::addUser();
        } else {
            //获取老用户的 id
            $uid = UserAccount::getUid($user_id);
            //以前的
            $login_type = 2;
        }

        //写入token
        TokenUser::joinToken($uid, $param_deviceid, $param_clienttime, time(), time());
        //返回对应的token
        $token = RsaDecode::tokenMake($uid);
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'login_type' => $login_type,
                'token' => $token,
                'user_info' => App::getUserProfile($user_id),
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
     * 忘记密码，加密的手机号，加密的新密码
     */
    public function actionForgetPwd() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数
        $param_deviceid = $request->post("deviceid");   //设备id
        $param_os = (int) $request->post("os");   //设备终端
        $param_mobile = $request->post("mobile");   //手机号
        $param_new_pwd = $request->post("new_pwd");   //新密码
        //获取token
        $headers = $request->headers;
        $header_openid = $headers->get('Openid') ? $headers->get('Openid') : $request->post("openid");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_deviceid, $param_mobile, $header_openid, $param_os, $param_new_pwd]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //验证加密
        $decode_hear_openid = RsaDecode::rsa_decode($header_openid);
        if ($decode_hear_openid !== $param_deviceid) {
            return $response->data = Error::errorJson($this_path, 9001);
        }//验证通过，证明了合法性
        //测试数据 18312376990  123123
        $decode_mobile = RsaDecode::rsa_decode($param_mobile);
        $decode_new_pwd = RsaDecode::rsa_decode($param_new_pwd);

        //验证手机号是不是进行过注册的操作
        $user_id = UserEmblem::checkExist($decode_mobile, 2);
        if ($user_id == 0) {
            //手机号不存在
            return $response->data = Error::errorJson($this_path, 4004);
        }
        //修改手机密码
        UserPassword::changePwd($user_id, $decode_new_pwd);
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'mobile' => $decode_mobile,
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
     * 修改密码,需要登录
     */
    public function actionEditPwd() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数,和其他的分页的一样
        $param_os = (int) $request->post("os");   //渠道
        $param_new_pwd = $request->post("new_pwd");   //新密码
        $param_old_pwd = $request->post("old_pwd");   //新密码
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->post("token");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //解密token
        if ($header_token == '233' && (YII_ENV_LOCAL == 'local' || YII_ENV_DEV )) {
            $uid = 1046;
        } else {
            $token_decode = RsaDecode::clientTokenToArray($header_token);
            if ($token_decode === FALSE) {
                //返回错误信息
                return $response->data = Error::errorJson($this_path, 9003);
            }
            //验证token
            $check_user_token = TokenUser::check($token_decode['uid'], $token_decode['deviceid'], $token_decode['logintime']);
            if ($check_user_token !== "0") {
                return $response->data = Error::errorJson($this_path, $check_user_token);
            }
            $uid = $token_decode['uid'];
        }
        $user_id=  UserAccount::getUserId($uid);
        //测试数据 18312376990  123123
        $decode_old_pwd = RsaDecode::rsa_decode($param_old_pwd);
        $decode_new_pwd = RsaDecode::rsa_decode($param_new_pwd);
        
        
        //判断是不是有手机号注册
        $types= UserEmblem::findAllWay($user_id);
        if(!in_array(2, $types)){
           //返回错误信息
            return $response->data = Error::errorJson($this_path, 4005);
        }
        
        //判断旧密码
        if(!UserPassword::check($user_id, $decode_old_pwd)){
             return $response->data = Error::errorJson($this_path, 4006);
        }
        
        //修改密码
        $update=UserPassword::changePwd($user_id, $decode_new_pwd);
        

        if(!$update){
            //修改密码错误
            return $response->data = Error::errorJson($this_path, 4007);
        }
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'user_info' => App::getUserProfile($user_id),
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
     * 验证手机号是不是已经被注册了
     */
    public function actionCheckMobile(){
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数
        $param_deviceid = $request->post("deviceid");   //设备id
        $param_os = (int) $request->post("os");   //设备终端
        $param_mobile = $request->post("mobile");   //手机号
        //获取token
        $headers = $request->headers;
        $header_openid = $headers->get('Openid') ? $headers->get('Openid') : $request->post("openid");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_deviceid, $param_mobile, $header_openid, $param_os]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //验证加密
        $decode_hear_openid = RsaDecode::rsa_decode($header_openid);
        if ($decode_hear_openid !== $param_deviceid) {
            return $response->data = Error::errorJson($this_path, 9001);
        }//验证通过，证明了合法性
        //测试数据 18312376990  123123
        $decode_mobile = RsaDecode::rsa_decode($param_mobile);

        //验证手机号是不是进行过注册的操作
        $user_id = UserEmblem::checkExist($decode_mobile, 2);
        if ($user_id == 0) {
            //手机号不存在
            $check=0;
        }else{
            $check=1;
        }
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'mobile' => $decode_mobile,
                'check'=>$check
            ],
            'version' => $this_allow_version,
            'error_code' => 0,
            'error' => "",
        ];
        //接口访问记录
        CensusApi::add($user_id, $this_path);
        return $response->data = $return_json;
    }

}
