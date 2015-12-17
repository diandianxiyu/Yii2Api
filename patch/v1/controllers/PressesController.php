<?php

/**
 * 
 * 稿件相关接口
 * 
 * 更新 
 *  2015-10-15 17:40:44  Calvin 创建 v1.1版本
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
use app\models\TokenUser;
use app\models\CensusApi;
use app\models\ArticleSection;
use app\models\ArticleItem;
use app\models\UserDetail;

class PressesController extends Controller {

    //定义本类的名称
    private $modules_name = 'v1';
    private $class_name = 'presses';
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
                    'press-list' => ['get'],
                    'item-list' => ['get'],
                ],
            ],
        ];
    }

    /**
     * 获取稿件列表，稿件按时间排序
     */
    public function actionPressList() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数,和其他的分页的一样
        $param_os = (int) $request->get("os");   //操作系统
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

        $list = ArticleSection::getPressList($param_timelimit, $param_count, $param_page);

        if (count($list) != 0) {
            //加入当前稿件的商品数量，是不是有 new 的标记
            foreach ($list as $key => $value) {
                $item_count = ArticleItem::getCountByTid($value['id']);
                $new_sign = (date("Y-m-d", $value['online_time']) == date("Y-m-d")) ? 'new' : '';
                unset($list[$key]['id']);
                $list[$key]['counts'] = $item_count;
                $list[$key]['sign'] = $new_sign;
            }
        }
        $next_page = ArticleSection::getPressListNextPage($param_timelimit, $param_count, $param_page) ? 1 : 0;
        //进行数据的获取
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

    /**
     * 获取稿件信息下的全部信息
     */
    public function actionItemList() {
        $response = Yii::$app->response;
        $response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;
        $this_path = $this->modules_name . "/" . lcfirst($this->class_name) . "/" . lcfirst(str_replace('action', "", __FUNCTION__));
        $this_allow_version = "1.1";
        //获取参数,和其他的分页的一样
        $param_os = (int) $request->get("os");   //操作系统
        $param_tid = (int) $request->get("tid");    //心愿清单的 id
        //获取token
        $headers = $request->headers;
        $header_token = $headers->get('Token') ? $headers->get('Token') : $request->get("token");    //加密之后的设备号，做到兼容post的方式
        //验证参数是不是有空的
        $check_null = Validators::validateAllNull([$param_tid, $param_os, $header_token]);
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

        $user_id = UserAccount::getUserId($uid);

        //判断是不是存在
        $exist = ArticleSection::checkExist($param_tid);
        if ($exist == FALSE) {
            //返回错误信息
            return $response->data = Error::errorJson($this_path, 6001);
        }
        //获取对应商品列表
        $list = ArticleItem::getList(ArticleSection::getArticleId($param_tid), $param_os, $uid);

        //循环，给出是不是已经加入了心愿清单，对应的返回结果

        if (count($list) != 0) {
            //加入当前稿件的商品数量，是不是有 new 的标记
            foreach ($list as $key => $value) {
                if ($value['open_iid'] != "") {
                    $detail = UserDetail::checkDetail($user_id, $value['open_iid'], $value['id']);
                    $list[$key]['detail_counts'] = UserDetail::getCountsByItemId( $value['id']);
                } else {
                    $detail = FALSE;
                    $list[$key]['detail_counts'] = 0;
                }
                //获取对
                unset($list[$key]['id']);
                unset($list[$key]['promotion_price']);
                $list[$key]['detail'] = $detail ? 1 : 0;
            }
        }
        //进行数据的获取
        //返回对应的结果
        $return_json = [
            'request' => $this_path,
            'info' => $list,
            'version' => $this_allow_version,
            'error_code' => 0,
            'error' => "",
        ];
        //接口访问记录
        CensusApi::add($user_id, $this_path);
        return $response->data = $return_json;
    }

}
