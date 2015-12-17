<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_password".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $pwd
 * @property integer $update_time
 */
class UserPassword extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_password';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'update_time'], 'integer'],
            [['pwd'], 'string', 'max' => 96]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'user_id' => '用户的 id',
            'pwd' => '密码',
            'update_time' => '更新',
        ];
    }
    
    /**
     * 加密
     * @param string $pwd
     */
    private static function __encode($pwd){
        return md5(base64_encode($pwd));
    }
    
    /**
     * 写入数据
     * @param  int $user_id
     * @param string $pwd
     */
    public static function add($user_id,$pwd){
        $model=new UserPassword();
        $model->pwd= self::__encode($pwd);
        $model->user_id=$user_id;
        $model->update_time=  time();
        $model->insert();
        return $model->id ;
    }
    /**
     * 验证密码是不是一致
     * @param  int $user_id 用户 id
     * @param int $pwd 密码
     */
    public static function check($user_id,$pwd){
        $info=  self::find()->where(['user_id'=>$user_id])->andWhere(['pwd'=>  self::__encode($pwd)])->one();
        if($info){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    
    /**
     * 修改账户的密码
     * @param  int $user_id 用户的 id
     * @param string $pwd 新密码
     */
    public static function changePwd($user_id,$pwd){
        $update=  self::updateAll([
            'pwd'=>  self::__encode($pwd),
            'update_time'=>  time()
        ],['user_id'=>$user_id]);
        if($update){
            return TRUE;
        }else{
            return FALSE;
        }
    }
}
