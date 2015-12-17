<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invite_code".
 *
 * @property integer $id
 * @property string $code
 * @property integer $user_id
 * @property integer $used
 * @property integer $used_user_id
 * @property integer $create_time
 * @property integer $used_time
 */
class InviteCode extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invite_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'used', 'used_user_id', 'create_time', 'used_time'], 'integer'],
            [['code'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'code' => '邀请码',
            'user_id' => '用户的id',
            'used' => '是否被使用了，默认0',
            'used_user_id' => '使用的用户',
            'create_time' => '创建时间',
            'used_time' => '使用的时间',
        ];
    }
    
    /**
     * 给用户添加
     * @param int $user_id 用户的id
     */
    public static function addCodeByUser($user_id){
        self::addCode($user_id);
        self::addCode($user_id);
    }

    /**
     * 添加用户的邀请码
     * @param int $user_id 用户的主键
     */
    public static function addCode($user_id){
        $model=new InviteCode();
        $model->code=  self::__getCode();
        $model->create_time=  time();
        $model->used=0;
        $model->used_time=0;
        $model->used_user_id=0;
        $model->user_id=$user_id;
        $model->insert();
        return $model->id;
    }
    
    /**
     * 获取验证码
     */
    private static function  __getCode(){
        $md5=  md5(microtime(TRUE));
        //截取字符串
        $md5_str=  substr($md5, rand(0, 5), 4);
        return $md5_str;
    }
    
    /**
     * 获取用户的邀请码
     * @param int $user_id  用户的主键
     */
    public static function getCodeList($user_id){
        $list=  self::find()->where(['user_id'=>$user_id])->all();
        $code=[];
        if($list){
            foreach ($list as $value) {
                $att=$value->attributes;
                $one=[];
                $one['code']=$att['code'];
                $one['used']=$att['used'];
                $code[]=$one;
            }
        }
        return $code;
    }
    

    /**
     * 看看这个邀请码是不是存在
     * @param string $code 邀请码
     */
    public static function checkCode($code){
        $info=  self::find()->where(['code'=>$code])->one();
        if($info){
            //返回全部的信息
            return $info->attributes;
        }else{
            return FALSE;
        }
    }
    
    /**
     * 使用验证码
     * @param string $code 验证码
     */
    public static function useCode($code,$user_id){
        $info= self::find()->where(['code'=>$code])->one();
        $info->used=1;
        $info->used_time=  time();
        $info->used_user_id=$user_id;
        $info->save();
        return $info->id;
       
    }
}
