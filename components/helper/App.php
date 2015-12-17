<?php

/**
 * 整合数据类
 * 
 *  用于直接返回应用内部的需要多个 model 组合的数据
 * 
 * @author Calvin 
 * @version 1.1
 * @copyright (c) 2015, jingyu 
 * 
 */

namespace app\components\helper;

use Yii;
use app\models\UserAccount;
use app\models\UserAvatar;
use app\models\UserProfile;
use app\models\UserStatus;
use app\models\SignRecord;
use app\models\TagInfo;
use app\models\ContentTagCount;
use app\models\ContentBase;
use app\models\ContentTagList;

require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/android/AndroidBroadcast.php');
require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/android/AndroidFilecast.php');
require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/android/AndroidGroupcast.php');
require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/android/AndroidUnicast.php');
require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/android/AndroidCustomizedcast.php');
require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/ios/IOSBroadcast.php');
require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/ios/IOSFilecast.php');
require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/ios/IOSGroupcast.php');
require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/ios/IOSUnicast.php');
require_once(dirname(__FILE__) . '/../umengsdk/' . 'notification/ios/IOSCustomizedcast.php');

class App {

    /**
     * 通过用户的 user_id获取用户的基本信息
     * @param  int $user_id 用户的 id
     */
    public static function getUserProfile($user_id) {
        //获取用户的昵称性别头像
        $user_avatar = UserAvatar::get($user_id);
        //用户基本信息
        $user_profile = UserProfile::getProfile($user_id);
        //获取 uid
        $uid = UserAccount::getUid($user_id);
        return array_merge(['uid' => $uid], $user_profile, ['avatar' => $user_avatar]);
    }

    /**
     * 1.3版本的通过用户的 user_id获取用户的基本信息
     * @param  int $user_id 用户的 id
     */
    public static function getUserProfileVer13($user_id) {
        //获取用户的昵称性别头像
        $user_avatar = UserAvatar::get($user_id);
        //用户基本信息
        $user_profile = UserProfile::getProfile($user_id);
        //认证状态
        $sign_status = UserStatus::checkSignStatus($user_id) ? 1 : 0;
        //禁言状态
        $nosay_status = UserStatus::checkPressContentStatus($user_id) ? 1 : 0;
        //返回签到天数
        $sign_count = SignRecord::getSignCount($user_id);
        //获取 uid
        $uid = UserAccount::getUid($user_id);
        //被点赞的总数，获取的顺序，发布中的数量，对应的使用的标签的数量，->太麻烦，临时增加数据表，做点赞总数的记录，用user_status的字段吧 = =
        $like_count = UserStatus::getUserLikeCount($user_id);
        //验证身份的数组
        $auth_user_arr = Yii::$app->params['auth_account'];
        //组合数据
        $user_status = [
            'uid' => $uid,
            'avatar' => $user_avatar,
            'sign_count' => $sign_count,
            'banned_status' => $nosay_status, //禁言状态
            'sign_status' => $sign_status,
            'like_count' => $like_count,
            'access_status' => 1,
            'account_status' => (int) in_array($uid, $auth_user_arr),
        ];

        $user_info = array_merge($user_status, $user_profile);
        return $user_info;
    }

    /**
     * 获取
     * @param type $content_cid
     * @param type $user_id
     */
    public static function getContentInfo($content_cid, $user_id, $tag_num = 5) {

        $content_id = ContentBase::getIdByCid($content_cid);
        //获取基本信息
        $content = ContentBase::getInfoByCid($content_cid);

        $user_info = self::getUserProfileVer13($content['user_id']);
        unset($content['user_id']);
        $tags = ContentTagCount::getTagList($content_id, $user_id, $tag_num, 0);
        $arr = [
            'content' => $content,
            'user_info' => $user_info,
            'tags' => $tags
        ];

        return $arr;
    }

    /**
     * 获取这个标签的全部信息
     * @param int $tag_id
     */
    public static function getTagInfo($tag_id) {
        //基本信息
        $base = TagInfo::getInfoById($tag_id);
        //统计信息 总数
        $extend = ContentTagCount::getTInfoByTagId($tag_id);
        $extend['like_count'] = (int) $extend['like_count'];
        $extend['content_count'] = (int) $extend['content_count'];
        return array_merge($base, $extend);
    }

    /**
     * 发送推送
     */
    public static function SendNotice($uid,$test_model=true) {
        $ex = [
            'send_type' => 5,
        ];

        //调用安卓的
        self::sendAndroidCustomizedcast(strval($uid), $ex,$test_model);
        self::sendIOSCustomizedcast(strval($uid), $ex,$test_model);
    }

    private static function sendAndroidCustomizedcast($uid, $ex,$test_model=1) {
        $appMasterSecret = "iieluvg1vpv5on6cerjwwzn5ztnhgzjy";
        $appkey = "55f0ee86e0f55acbfd002371";
        try {
            $customizedcast = new \AndroidCustomizedcast();
            $customizedcast->setAppMasterSecret($appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey", $appkey);
            $customizedcast->setPredefinedKeyValue("timestamp", strval(time()));
            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then 
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("alias", $uid);
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type", "UID");
            $customizedcast->setPredefinedKeyValue('display_type', 'message');
            // Set 'production_mode' to 'false' if it's a test device. 
            // For how to register a test device, please see the developer doc.

            if ($test_model ) {
                $customizedcast->setPredefinedKeyValue("production_mode", "false");
            } else {
                $customizedcast->setPredefinedKeyValue("production_mode", "true");
            }




            $customizedcast->setPredefinedKeyValue("custom", json_encode($ex));
            // [optional]Set extra fields
            $customizedcast->setPredefinedKeyValue('description', 'V1.3' . date("Y-m-d H:i:s"));
//            foreach ($ex as $key => $value) {
//                // var_dump($key);
//                //  echo "<br/>";
//                //  var_dump($value);
//                //  echo "<br/>";
//                $customizedcast->setExtraField((string) $key, $value);
//            }
//            print("Sending customizedcast notification, please wait...\r\n");
            $customizedcast->send();
//            print("Sent SUCCESS\r\n");
        } catch (\Exception $e) {

//             print("Caught exception: " . $e->getMessage());
        }
    }

    private static function sendIOSCustomizedcast($uid, $ex,$test_model=1) {
        $appMasterSecret = "aekwnddph20oq23mgubtgva268xpy1ms";
        $appkey = "55f0efc4e0f55acbfd0027e0";
        try {
            $customizedcast = new \IOSCustomizedcast();
            $customizedcast->setAppMasterSecret($appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey", $appkey);
            $customizedcast->setPredefinedKeyValue("timestamp", strval(time()));

            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then 
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("alias", $uid);
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type", "UID");
//            $customizedcast->setPredefinedKeyValue("alert", "");
            $customizedcast->setPredefinedKeyValue("badge", 0);
//            $customizedcast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode


            if ($test_model) {
                $customizedcast->setPredefinedKeyValue("production_mode", "false");
            } else {
                $customizedcast->setPredefinedKeyValue("production_mode", "true");
            }

            $customizedcast->setPredefinedKeyValue('display_type', 'message');
            $customizedcast->setPredefinedKeyValue('description', 'V1.3' . date("Y-m-d H:i:s"));
            foreach ($ex as $key => $value) {
                // var_dump($key);
                //  echo "<br/>";
                //  var_dump($value);
                //  echo "<br/>";
                $customizedcast->setCustomizedField((string) $key, $value);
            }
            //print("Sending customizedcast notification, please wait...\r\n");
            $customizedcast->send();
            // print("Sent SUCCESS\r\n");
        } catch (\Exception $e) {
            // print("Caught exception: " . $e->getMessage());
        }//
    }

}
