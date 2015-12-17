<?php

/**
 * 应用内的rsa加密解密类
 * 
 * @author calvin
 * @version 2.1
 * @created 2015-4-2
 */

namespace app\components\helper;

use app\components\helper\Rsa;

class RsaDecode extends Rsa {

    /**
     * 加密
     * @param type $string
     */
    public static function rsa_encode($string) {
        return parent::privEncrypt($string);
    }

    /**
     * 解密
     * @param type $string
     */
    public static function rsa_decode($string) {
        return parent::privDecrypt($string);
    }

    /**
     * 将token解析成数组
     * @param type $token
     */
    public static function clientTokenToArray($token) {
        if ($token == null || $token == "") {
            return false;
        }
        //截取字符串
        $arr = explode("==", $token);
        if(count($arr) != 3){
            return FALSE;
        }
        $a_token = $arr[0]."==";
        $b_token = $arr[1]."==";
        //解码
        $a_token_str = self::rsa_decode($a_token);
        $b_token_str = self::rsa_decode($b_token);
        //保证分割符必须存在，且只有一个
        if ((stripos($a_token_str, "&") + strrpos($b_token_str, "&")) > 0 && (stripos($a_token_str, "&") == strrpos($a_token_str, "&")) && (stripos($b_token_str, "&") == strrpos($b_token_str, "&"))) {
            $a_token_arr = explode("&", $a_token_str);
            $b_token_arr = explode("&", $b_token_str);
            //合并数组
            $big_arr = array_merge($a_token_arr, $b_token_arr);
            //替换下标
            $result['uid'] = $big_arr[0];
            $result['logintime'] = $big_arr[1];
            $result['deviceid'] = $big_arr[2];
            $result['clienttime'] = $big_arr[3];
        } else {
            $result = FALSE;
        }
        return $result;
    }

    /**
     * 获取用户登录后返回的token
     * @param int $uid   用户的uid
     * @param int $time 登录的时间，默认是现在
     */
    public static function tokenMake($uid, $time = "") {
        if ($time == "") {
            //如果时间输入空，直接赋值给服务器当前相应时间
            $time = time();
        }
        $str = $uid . "&" . $time;
        return self::rsa_encode($str);
    }

    /**
     * 同时支持 utf-8、gb2312都支持的汉字截取函数 ,默认编码是utf-8
     * 
     * @param string $string  要截取的字符串
     * @param int $sublen   截取的长度
     * @param int $start   开始的地方
     * @param bool $isext    后面显示出来的。。。。
     * @param string $code   编码
     * @return string    截取之后的编码
     */
    private static function __cut_str($string, $sublen, $start = 0, $isext = FALSE, $code = 'UTF-8') {
        if ($code == 'UTF-8') {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
            preg_match_all($pa, $string, $t_string);
            if (count($t_string[0]) - $start > $sublen) {
                if ($isext) {
                    return join('', array_slice($t_string[0], $start, $sublen)) . "...";
                }
            }
            return join('', array_slice($t_string[0], $start, $sublen));
        } else {
            $start = $start * 2;
            $sublen = $sublen * 2;
            $strlen = strlen($string);
            $tmpstr = '';
            for ($i = 0; $i < $strlen; $i++) {
                if ($i >= $start && $i < ($start + $sublen)) {
                    if (ord(substr($string, $i, 1)) > 129) {
                        $tmpstr.= substr($string, $i, 2);
                    } else {
                        $tmpstr.= substr($string, $i, 1);
                    }
                }
                if (ord(substr($string, $i, 1)) > 129)
                    $i++;
            }
            if (strlen($tmpstr) < $strlen) {
                if ($isext) {
                    $tmpstr.= "...";
                }
            }
            return $tmpstr;
        }
    }

}
