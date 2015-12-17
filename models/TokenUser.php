<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "token_user".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $logintime
 * @property string $deviceid
 * @property integer $clienttime
 * @property integer $requesttime
 */
class TokenUser extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'token_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'logintime', 'deviceid', 'clienttime', 'requesttime'], 'required'],
            [['uid', 'logintime', 'clienttime', 'requesttime'], 'integer'],
            [['deviceid'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'uid' => '用户uid',
            'logintime' => '登录时间',
            'deviceid' => '设备号',
            'clienttime' => '客户端时间',
            'requesttime' => '响应时间',
        ];
    }
    
     /**
     * 把数据写入认证表中
     * @param  int  $uid 用户uid 
     * @param string $deviceid  设备号
     * @param int $clienttime   客户端传过来的时间
     * @param int $logintime    登录时间
     * @param int $requesttime   接口最后响应时间
     */
    public static function joinToken($uid, $deviceid, $clienttime, $logintime, $requesttime) {
        //只删除这个账户，这个设备的记录
        self::deleteAll("uid = :uid  and deviceid = :did", array(":uid" => $uid, ":did" => $deviceid));
        //写入记录
        $step2 = new TokenUser();
        $step2->uid = $uid;
        $step2->clienttime = $clienttime;
        $step2->logintime = $logintime;
        $step2->requesttime = $requesttime;
        $step2->deviceid = $deviceid;
        $step2->save();
        return $step2->id;
    }

    /**
     * 验证token
     */
    public static function check($uid, $deviceid, $logintime) {
        //先进行
        $step1 = self::find()->select(['id'])->where("uid = :uid", [':uid' => $uid])->one();
        if ($step1 === NULL) {
            //token无效
            return 9004;
        }
        //登录有效期，暂时不用
//        $this_request_time=$step1['requesttime'];
//        if(time() - $this_request_time >\Yii::$app->params['requestTime']){
//            return "90005";
//        }
        //刷新
//        self::refreshUserToken($uid, $deviceid);
        return "0";
    }
}
