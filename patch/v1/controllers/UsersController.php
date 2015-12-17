<?php

/**
 * 
 * 用户相关接口
 * 
 * 更新 
 *  2015-10-10 15:52:51  Calvin 创建
 * 
 */

namespace app\patch\v1\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\helper\RsaDecode; //加密相关
use app\components\helper\Validators; //验证相关
use app\components\error\Error; //错误返回
use app\models\UserAccount;
use app\models\UserAvatar;
use app\models\TokenUser;
use app\models\CensusApi;
use app\components\helper\WebImgToLocal;
use app\models\UserProfile;
use app\components\helper\App;

class UsersController extends Controller {

    //定义本类的名称
    private $modules_name = 'v1';
    private $class_name = 'users';
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
                    'edit' => ['post'],
                ],
            ],
        ];
    }

    /**
     * 修改用户头像昵称性别，post 表单提交
     */
    public function actionEdit() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数
        $param_os = (int) $request->post("os");   //系统
        $param_gender = (int) $request->post("gender");   //性别 1男 2女
        $param_nikename = $request->post("nikename");   //新的用户昵称
        $param_avatar = isset($_FILES['avatar']['error']);   //头像上传，如果值是 true 再看 error 是不是0，如果是0就表示有对应的头像上传
        if ($param_avatar == true && $_FILES['avatar']['error'] == 0) {
            $param_avatar = TRUE;  //头像存在
        } else {
            $param_avatar = null;  //头像不存在
        }
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->post("token");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }

        //验证三个参数是不是至少有一个
        $check_all_null = Validators::validateNotAllNull([$param_gender, $param_nikename, $param_avatar]);
        if ($check_all_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1002);
        }
        //测试环境本地环境对 token 不做验证
        if ($header_token == '233' && (YII_ENV_LOCAL == 'local' || YII_ENV_DEV )) {
            $uid = 1047;
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

        //性别
        if ($param_gender) {
            $update_gender = UserProfile::editGender($user_id, $param_gender);

            //验证性别
            if (!in_array($param_gender, [1, 2])) {
                //性别输入不符
                return $response->data = Error::errorJson($this_path, 4008);
            }
            if (!$update_gender) {
                return $response->data = Error::errorJson($this_path, 5001);
            }
        }

        //昵称
        if ($param_nikename) {
            $update_nikename = UserProfile::editNikename($user_id, $param_nikename);
            if (!$update_nikename) {
                return $response->data = Error::errorJson($this_path, 5001);
            }
        }

        //头像
        if ($param_avatar) {
            $avatar_url = WebImgToLocal::simpleUploadV2($_FILES['avatar']);
            UserAvatar::changeAvatar($user_id, $avatar_url);
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

}
