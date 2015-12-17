<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_account".
 *
 * @property integer $id
 * @property integer $uid
 * @property integer $create_time
 * @property string $create_channel
 * @property integer $create_os
 */
class UserAccount extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'create_time', 'create_channel', 'create_os'], 'required'],
            [['uid', 'create_time', 'create_os'], 'integer'],
            [['create_channel'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'uid' => '用户id',
            'create_time' => '注册时间',
            'create_channel' => '注册渠道',
            'create_os' => '设备，1安卓，2iOS',
        ];
    }
    
    /**
     * 对用户的账户表中添加数据
     * @param int $uid 用户uid
     * @param string $channel 注册的渠道
     * @param int $os 注册的操作系统 1安卓，2iOS
     * @return int user_id
     */
    public static function add($uid,$channel,$os){
        $info=new UserAccount();
        $info->uid=$uid;
        $info->create_time=  time();
        $info->create_channel=$channel;
        $info->create_os=$os;
        $info->insert();
        return $info->id;
    }
    
    /**
     * 获取用户的uid
     * @param int $id
     */
    public static function getUid($id){
        $info=self::find()->where(['id'=>$id])->select(['uid'])->one();
        return $info['uid'];
    }
    
    /**
     * 获取用户账户的主键id
     * @param int $id
     */
    public static function getUserId($uid){
        $info=self::find()->where(['uid'=>$uid])->select(['id'])->one();
        if($info){
            return $info['id'];
        }
        return FALSE;
        
    }
    
}
