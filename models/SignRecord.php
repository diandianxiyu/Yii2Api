<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sign_record".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $sign_time
 * @property string $sigin_date
 * @property integer $status
 */
class SignRecord extends \yii\db\ActiveRecord
{
    const SIGN_LIMIT=3;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sign_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'sign_time', 'status'], 'integer'],
            [['sigin_date'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'user_id' => '用户id',
            'sign_time' => '签到时间',
            'sigin_date' => '签到日期',
            'status' => '默认1',
        ];
    }
    
    /**
     * 获取用户的签到数量 
     * @param int $user_id 用户的主键
     */
    public static function getSignCount($user_id){
        return (int)  self::find()->where(['user_id'=>$user_id])->count();
    }
    
    /**
     * 签到一次
     * @param int $user_id 用户的uid
     */
    public static function SignInByUser($user_id){
        $model=new SignRecord();
        $model->sigin_date=  date("Y-m-d");
        $model->sign_time=  time();
        $model->status=1;
        $model->user_id=$user_id;
        $model->insert();
        return $model->id;
    }
    
    /**
     * 判断进行是不是已经签到
     * @param int $user_id 用户uid
     */
    public static function checkToday($user_id){
        $info=  self::find()->where(['user_id'=>$user_id,'sigin_date'=> date("Y-m-d")])->one();
        if($info){
            return TRUE;
        }
        return FALSE;
    }
}
