<?php
/**
 * @author alun (http://alunblog.duapp.com)
 * @version 1.0
 * @created 2013-5-17
 */
 
namespace app\components\helper;

class Rsa
{
private static $PRIVATE_KEY = '-----BEGIN RSA PRIVATE KEY-----
xxxxxxxxxxxxxx
-----END RSA PRIVATE KEY-----
';
    /**
    *返回对应的私钥
    */
    private static function getPrivateKey(){
    
        $privKey = self::$PRIVATE_KEY;
         
        return openssl_pkey_get_private($privKey);      
    }
 
    /**
     * 私钥加密
     */
    public static function privEncrypt($data)
    {
        if(!is_string($data)){
                return null;
        }           
        return openssl_private_encrypt($data,$encrypted,self::getPrivateKey())? base64_encode($encrypted) : null;
    }
    
    
    /**
     * 私钥解密
     */
    public static function privDecrypt($encrypted)
    {
        if(!is_string($encrypted)){
                return null;
        }
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey()))? $decrypted : null;
    }
}
 
?>