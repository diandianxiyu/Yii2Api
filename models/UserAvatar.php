<?php

namespace app\models;

use Yii;
use app\models\AppDefined;

/**
 * This is the model class for table "user_avatar".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $avatar
 * @property integer $status
 * @property integer $update_time
 */
class UserAvatar extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_avatar';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'avatar', 'update_time'], 'required'],
            [['user_id', 'status', 'update_time'], 'integer'],
            [['avatar'], 'string', 'max' => 120]
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
            'avatar' => '头像的地址',
            'status' => '状态，默认1使用',
            'update_time' => '更新的时间',
        ];
    }
    
    /**
     * 初始化头像
     * 
     * @param type $user_id
     * @param type $avatar_url
     * @return type
     */
    public static function add($user_id,$avatar_url){
        //默认写入新的头像
        $model=new  UserAvatar();
        $model->user_id=$user_id;
        $model->status=1;
        $model->update_time=  time();   
        $model->avatar=$avatar_url;
        $model->insert();
        return $model->id;
    }
    
    /**
     * 获取用户的头像
     * @param int $user_id
     */
    public static function get($user_id){
        $info=self::find()->select(['avatar'])->where(['user_id'=>$user_id])->andWhere(['status'=>1])
              ->one();
//      return $info['avatar'];
        return  str_replace(\Yii::$app->params['oss_source_url'], \Yii::$app->params['oss_for_cdn_url'], $info['avatar']);
    }
    
    /**
     * 修改用户的头像
     * @param  int $user_id 
     * @param string $avatar_url
     */
    public static function changeAvatar($user_id,$avatar_url){
        //设置之前的头像禁用
        self::updateAll(['status'=>0], ['user_id'=>$user_id]);
        //添加新的头像地址
        return self::add($user_id, $avatar_url);
    }
    
}
