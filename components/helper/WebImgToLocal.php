<?php

/**
 * 把网络的图片保存本地并上传到网络返回地址
 * 
 * @author Calvin 
 * @version 2.1
 * @copyright (c) 2015, jingyu 
 */

namespace app\components\helper;

use Yii;
use app\components\helper\CurlPic;
use Aliyun\OSS\OSSClient;

class WebImgToLocal {

    /**
     * 把本地的图片或者网络图片保存到本地，然后上传到OSS，返回对应的地址
     * @param string $file 文件保存之后的$file 的文章
     * @param string $file_type  图片文件类型 ，默认为 jpeg
     * @return string 返回 OSS 的图片地址
     */
    public static function web2oss($weburl) {
        $img_name = md5(rand(1000000, 9999999) + rand(10, 99)) . ".png";
        $local_path = CurlPic::getImg($weburl, $img_name);
        //阿里云地址
        $ossClient = OSSClient::factory(array(
                    'AccessKeyId' => Yii::$app->params['keyId'],
                    'AccessKeySecret' => Yii::$app->params['keySecret'],
                    'Endpoint' => Yii::$app->params['Endpoint'],
        ));
        $key = $img_name;  //ob的名字
        $ossClient->putObject(array(
            'Bucket' => Yii::$app->params['classPicBucket'],
            'Key' => $key,
            'Content' => fopen($local_path, 'r'),
            'ContentLength' => filesize($local_path)
        ));
        $ossUrl = "http://" . Yii::$app->params['classPicBucket'] . ".oss-cn-qingdao.aliyuncs.com/" . $key;
        return $ossUrl;
    }


    /**
     * 本地文件上传
     * @param string  $local_path  本地文件地址
     * 
     * @return string 阿里云OSS图片地址
     */
    public static function simpleUpload($local_path) {
        $img_name = end(explode("/", $local_path));
        //阿里云地址
        $ossClient = OSSClient::factory(array(
                    'AccessKeyId' => Yii::$app->params['keyId'],
                    'AccessKeySecret' => Yii::$app->params['keySecret'],
                    'Endpoint' => Yii::$app->params['Endpoint'],
        ));
        $ossClient->putObject(array(
            'Bucket' => Yii::$app->params['classPicBucket'],
            'Key' => $img_name,
            'Content' => fopen($local_path, 'r'),
            'ContentLength' => filesize($local_path)
        ));
        $url = "http://" . Yii::$app->params['classPicBucket'] . ".oss-cn-qingdao.aliyuncs.com/" . $img_name;
        return $url;
    }
    
   /**
     * 临时文件夹直接上传到oss上
     * @param array  $file_param   $file_param['name'] 对应的临时文件夹的文件名 $file_param['tmp_name'] 临时文件夹的路径
     * 
     * @return string 阿里云OSS图片地址
     */
    public static function simpleUploadV2($file_param) {
        $img_name =  md5('pico'.microtime()).'.'. end(explode(".", $file_param['name']));
        //阿里云地址
        $ossClient = OSSClient::factory(array(
                    'AccessKeyId' => Yii::$app->params['keyId'],
                    'AccessKeySecret' => Yii::$app->params['keySecret'],
                    'Endpoint' => Yii::$app->params['Endpoint'],
        ));
        $ossClient->putObject(array(
            'Bucket' => Yii::$app->params['classPicBucket'],
            'Key' => $img_name,
            'Content' => fopen($file_param['tmp_name'], 'r'),
            'ContentLength' => filesize($file_param['tmp_name'])
        ));
        $url = "http://" . Yii::$app->params['classPicBucket'] . ".oss-cn-qingdao.aliyuncs.com/" . $img_name;
        return $url;
    }

}
