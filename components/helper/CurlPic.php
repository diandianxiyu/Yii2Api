<?php

/**
 * 远程获取图片并保存本地
 * 
 * @author Calvin 
 * @version 2.1
 * @copyright (c) 2015, jingyu 
 */

namespace app\components\helper;

use Yii;

class CurlPic {
    
    /*
     * @通过curl方式获取指定的图片到本地
     * @ 完整的图片地址
     * @ 要存储的文件名
     */
    public static function getImg($url = "", $filename = "",$extend_path="") {
        //去除URL连接上面可能的引号
        //$url = preg_replace( '/(?:^['"]+|['"/]+$)/', '', $url );
        $hander = curl_init();
        //获取今天的日期，然后进行拼接，创建路径，保存图片
        $data = date("Y", time()) . '/' . date("m", time()) . '/' . date("d", time()) . '/';
        if($extend_path !=""){
            $extend_path.="/";
        }
        $curl_path=Yii::$app->params['curl_pic_dir'];
        $save_path = $curl_path.$data.$extend_path;
        //创建相应了、目录
        if (!file_exists($save_path)) {
            self::__mkdirs($save_path);
        }
        $fp = fopen($save_path.$filename, 'wb');
        curl_setopt($hander, CURLOPT_URL, $url);
        curl_setopt($hander, CURLOPT_FILE, $fp);
        curl_setopt($hander, CURLOPT_HEADER, 0);
        curl_setopt($hander, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($hander,CURLOPT_RETURNTRANSFER,false);//以数据流的方式返回数据,当为false是直接显示出来
        curl_setopt($hander, CURLOPT_TIMEOUT, 60);
        curl_exec($hander);
        curl_close($hander);
        fclose($fp);
        return $save_path.$filename; 
    }

    /**
     * 递归创建目录
     * @param type $path
     * @param type $mode
     */
    private static function __mkdirs($path, $mode = 0777) { //creates directory tree recursively 
        $dirs = explode('/', $path);
        $pos = strrpos($path, ".");
        if ($pos === false) { // note: three equal signs 
            $subamount = 0;
        } else {
            $subamount = 1;
        }

        for ($c = 0; $c < count($dirs) - $subamount; $c++) {
            $thispath = "";
            for ($cc = 0; $cc <= $c; $cc++) {
                $thispath.=$dirs[$cc] . '/';
            }
            if (!file_exists($thispath)) {
                mkdir($thispath, $mode);
            }
        }
    }

}
