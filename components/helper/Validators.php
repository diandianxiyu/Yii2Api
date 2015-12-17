<?php

/**
 * 参数校验类
 * 
 *  用于验证一些和业务逻辑不相关的数据
 * 
 * @author Calvin 
 * @version 1.0
 * @copyright (c) 2015, jingyu 
 * 
 * 更新
 *  2015-09-11 15:23:01 迁移到新项目
 */

namespace app\components\helper;

use Yii;

class Validators {

    /**
     * 验证 全部的里面是不是有空的 
     * 
     * @param array $param 要验证的放到数组中
     * @return bool TRUE,表示不是空的；FALSE,表示里面的值有空值
     */
    public static function validateAllNull($param = []) {
        if (count($param) === 0) {
            return FALSE;
        }
        foreach ($param as $value) {
            if ($value == NULL || $value == "") {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * 验证 至少有一个不是空值
     * @param array $param 检查的参数放入数组中
     * @return bool TRUE,表示参数里面至少有一个不是空的；FASLE,表示参数里面全部都是空值
     */
    public static function validateNotAllNull($param = []) {
        $count = count($param);
        if ($count === 0) {
            return FALSE;
        }
        $h = 0;
        foreach ($param as $value) {
            if ($value !== NULL && $value !== "" && $value != FALSE) {
                $h ++;
            }
        }
        if ($h > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /**
     * 替换地址
     * @param string $url 网址
     * @param int $type 类型 1图片 2文件
     */
    public static function replaceWords($url,$type=1){
         if($type == 1){
             return str_replace(Yii::$app->params['img_url'], Yii::$app->params['img_cdn'], $url);
         }
         if($type ==2){
             return str_replace(Yii::$app->params['file_url'], Yii::$app->params['file_cdn'], $url);
         }
    }

}
