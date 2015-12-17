<?php

/**
 *
 * 用户心愿清单模块
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
use app\models\ArticleItem;
use app\models\UserDetail;
use app\models\ArticleGoods;
use app\models\CensusWishlist;

class DetailsController extends Controller {

    //定义本类的名称
    private $modules_name = 'v1';
    private $class_name = 'details';
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
                ],
            ],
        ];
    }

    /**
     * 在商品详情页面，点击心形，把带有商品信息的商品详情，加入心愿清单
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
        $param_add = (int) $request->post("add");   //是不是做添加操作
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->post("token");    //加密之后的设备号，做到兼容post的方式
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
        $item_info = ArticleItem::getInfoByIid($param_iid);
        if ($item_info == FALSE) {
            return $response->data = Error::errorJson($this_path, 6002);
        }

        //判断稿件是不是存在商品
        if ($item_info['open_iid'] == "") {
            return $response->data = Error::errorJson($this_path, 6002);
        }

        //根据uid获取user_id
        $user_id = UserAccount::getUserId($uid);

        //进行操作
        //返回结果
        //判断是不是已经加入了心愿清单
        if (UserDetail::checkDetail($user_id, $item_info['open_iid'], $item_info['id'])) {
            if ($param_add !== 1) {
                //删除
                UserDetail::del($user_id, $item_info['id']);
                CensusWishlist::delRecord($item_info['id']);
            }
        } else {
            if ($param_add == 1) {
                //添加
                UserDetail::add($user_id, $item_info['open_iid'], $item_info['id']);
                CensusWishlist::addRecord($item_info['id']);
            }
        }


        //返回当前的数量
        $counts = UserDetail::getCountsByItemId($item_info['id']);

        //判断是不是已经加入了心愿清单
        if (UserDetail::checkDetail($user_id, $item_info['open_iid'], $item_info['id'])) {
            //返回错误信息
            $status = 1;
        } else {
            $status = 0;
        }

        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
                'iid' => $item_info['iid'],
                'status' => $status,
                'counts' => $counts
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
     * 获取列表
     */
    public function actionList() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数,和其他的分页的一样
        $param_os = (int) $request->get("os");   //渠道
        $param_timelimit = $request->get("timelimit") ? $request->get("timelimit") : time();   //访问第一页的时候的时间戳
        $param_count = $request->get("count") ? $request->get("count") : 10;   //每页返回的数量，默认10
        $param_page = $request->get("page") ? $request->get("page") : 0;   //页码，默认第一页
        $param_uid = $request->get("uid") ? $request->get("uid") : 0;   //用户的uid，用来获取别人的信息
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
            $uid = 1801;
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

//如果不是自己
        if ($param_uid != 0) {
            $get_user_id = UserAccount::getUserId($param_uid);

            if (!$get_user_id) {
                //返回错误信息。这个人不存在
                return $response->data = Error::errorJson($this_path, 7008);
            }
        } else {
            $get_user_id = $user_id;
        }

        //获取弹幕的列表
        $list = UserDetail::getList($get_user_id, $param_timelimit, $param_count, $param_page);
        //返回这个单个的被收藏的数据
        /*
         * 淘宝或者天猫
         * open_iid
         * 心愿清单的缩略图
         * 促销价
         * 原价
         * 加入时间
         * 稿件标题
         */

        //组装用户信息
        foreach ($list as $key => $value) {
            //商品信息
            $item_info = ArticleItem::findOne(['id' => $value['item_id']]);
            $list[$key]['thumb_detail'] = $item_info->thumb_page;
            $list[$key]['title'] = $item_info->title;
            //淘宝信息
            $shop_info = ArticleGoods::getInfoById($value['open_iid']);
            $list[$key]['price'] = $shop_info['price'];
            $list[$key]['discount_price'] = $shop_info['discount_price'];
            $list[$key]['shop_type'] = (int) $shop_info['shop_type'];
            //去掉 iid
            $list[$key]['iid'] = $item_info['iid'];
            unset($list[$key]['item_id']);
        }
        //是否还有下一页
        $next_page = UserDetail::getListNextPage($get_user_id, $param_timelimit, $param_count, $param_page) ? 1 : 0;

        // var_dump($param_timelimit);        exit();
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => [
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

}
