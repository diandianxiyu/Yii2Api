<?php

/**
 *
 * 商品的评论
 *
 * 2015-10-19 10:34:48
 * Calvin
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
use app\models\CensusApi;
use app\models\UserAccount;
use app\models\CensusItemComments;
use app\models\ArticleItem;
use app\models\ArticleItemComment;
use app\components\helper\App;

class ItemCommentsController extends Controller {

    //定义本类的名称
    private $modules_name = 'v1';
    private $class_name = 'item-comments';
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
                    'add' => ['post'],
                    'list' => ['get'],
                    'del' => ['post'],
                ],
            ],
        ];
    }

    /**
     * 给一个稿件发布弹幕
     */
    public function actionAdd() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数,和其他的分页的一样
        $param_os = (int) $request->post("os");   //渠道
        $param_iid = $request->post("iid");    //稿件的id
        $param_text = $request->post("text");   //弹幕的文本
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->post("token");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token, $param_iid, $param_text]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //解密token
        if ($header_token == '233' && (YII_ENV_LOCAL == 'local' || YII_ENV_DEV )) {
            $uid = 1049;
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

        //判断稿件是不是已经存在，并返回 id
        $item_id = ArticleItem::getIdByIid($param_iid);
        if ($item_id == FALSE) {
            return $response->data = Error::errorJson($this_path, 6002);
        }

        //根据uid获取user_id
        $user_id = UserAccount::getUserId($uid);

        //写入
        $comment_id = ArticleItemComment::addComment($item_id, $user_id, $param_text);

        //评论统计
        CensusItemComments::addRecord($item_id);

        //返回详情
        $comment_info = ArticleItemComment::getOneComment($comment_id);


        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'comment_info' => $comment_info,
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
     * 获取弹幕列表
     */
    public function actionList() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数,和其他的分页的一样
        $param_os = (int) $request->get("os");   //渠道
        $param_iid = $request->get("iid");    //稿件的id
        $param_timelimit = $request->get("timelimit") ? $request->get("timelimit") : time();   //访问第一页的时候的时间戳
        $param_count = $request->get("count") ? $request->get("count") : 10;   //每页返回的数量，默认10
        $param_page = $request->get("page") ? $request->get("page") : 0;   //页码，默认第一页
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->get("token");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token, $param_iid]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //解密token
        if ($header_token == '233' && (YII_ENV_LOCAL == 'local' || YII_ENV_DEV )) {
            $uid = 1048;
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
        //判断稿件是不是已经存在，并返回 id
        $item_id = ArticleItem::getIdByIid($param_iid);
        if ($item_id == FALSE) {
            return $response->data = Error::errorJson($this_path, 6002);
        }

        //根据uid获取user_id
        $user_id = UserAccount::getUserId($uid);

        //获取弹幕的列表
        $comments = ArticleItemComment::getList($item_id, $param_timelimit, $param_count, $param_page);


        $comment_list = [];
        //组装用户信息
        foreach ($comments as $value) {
            $comment = [
                'comment_info' => $value,
                'user_info' => App::getUserProfile(UserAccount::getUserId($value['uid'])),
            ];
            $comment_list[] = $comment;
        }
        //是否还有下一页
        $next_page = ArticleItemComment::getNextPage($item_id, $param_timelimit, $param_count, $param_page) ? 1 : 0;
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'list' => $comment_list,
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
     * 删除自己的评论
     */
    public function actionDel() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数,和其他的分页的一样
        $param_os = (int) $request->post("os");   //渠道
        $param_id = (int)$request->post("id");    //评论的 id
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->post("token");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_os, $header_token, $param_id]);
        if ($check_null === FALSE) {
            //错误信息，参数不全
            return $response->data = Error::errorJson($this_path, 1001);
        }
        //解密token
        if ($header_token == '233' && (YII_ENV_LOCAL == 'local' || YII_ENV_DEV )) {
            $uid = 1049;
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
        
        //根据uid获取user_id
        $user_id = UserAccount::getUserId($uid);
        
        //评论是不是存在,并返回作者
        $comment_uid = ArticleItemComment::getUserIdById($param_id);
        if ($comment_uid == FALSE) {
            return $response->data = Error::errorJson($this_path, 6003);
        }
        
        //判断是不是这个人的评论
        if($user_id != $comment_uid){
            return $response->data = Error::errorJson($this_path, 6004);
        }
        
        //执行删除操作
        ArticleItemComment::del($param_id);


        //删除
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' =>$param_id,
            'version' => $this_allow_version,
            'error_code' => 0,
            'error' => "",
        ];
        //接口访问记录
        CensusApi::add($user_id, $this_path);
        return $response->data = $return_json;
    }

}
