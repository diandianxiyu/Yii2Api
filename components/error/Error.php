<?php

/**
 * 错误处理类
 * 
 * ------------------------------------
 * 
 * 错误处理扩展 seaslog
 * 
 * http://www.oschina.net/news/50333/seaslog-0-21
 * 
 * http://neeke.github.io/SeasLog/
 * 
 * 修改写入日志方式，TAE环境下sealog不可用
 * ------------------------------------
 * 
 * @author Calvin 
 * @version 2.1
 * @copyright (c) 2015, jingyu 
 */

namespace app\components\error;

use Yii;

class Error {

    public static function errorJson($request, $error) {
        $requests = Yii::$app->request;
        $post = json_encode($requests->post());
        $get = json_encode($requests->get());
        $headers = json_encode($requests->getHeaders()->toArray());
        $error_file = require_once \Yii::$app->basePath . "/components/error/ErrorCode.php";
        $arr = [
            'request' => $request,
            'error_code' => $error,
            'error' => $error_file["$error"].'post-> '.$post.'|||| get->  '.$get.' |||   header->'.$headers,
        ];

//        $userIP = $requests->userIP;
        //写入日志
//        \SeasLog::error('error === {error} && error_text === {error_text}  && userIP === {userIP} && post === {post}  && get === {get} && header === {header} ', [
//            '{error}' => $error,
//            '{error_text}' => $error_file["$error"],
//            '{post}' => $post,
//            '{get}' => $get,
//            '{userIP}' => $userIP,
//            '{header}' => $headers,
//                ], $request);
//        $appLog = \Alibaba::AppLog();
//        $appLog->debug("debug-log-emssage");
//        $appLog->info("info-log-emssage");
//        $appLog->warn("warn-log-emssage");
//        $appLog->error("error-log-emssage");
        return $arr;
    }

}
