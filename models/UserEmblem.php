<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_emblem".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $login_type
 * @property string $login_value
 * @property integer $create_time
 */
class UserEmblem extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user_emblem';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'login_type', 'login_value', 'create_time'], 'required'],
            [['user_id', 'login_type', 'create_time'], 'integer'],
            [['login_value'], 'string', 'max' => 96]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'user_id' => '用户的id',
            'login_type' => '登录身份
1设备
2手机
3QQ
4微博
5微信',
            'login_value' => '对应的值',
            'create_time' => '绑定的时间',
        ];
    }

    /**
     * 检查是不是存在这个方式的登录
     * @param type $value 验证的值
     * @param int $type 登录的类型 1设备 2手机 3QQ 4微博 5微信 6 淘宝
     * @return int 0,表示这个没有被注册；其他的表示有账户，会返回对应的用户的id
     */
    public static function checkExist($value, $type = 1) {
        $user = self::find()->where(['login_type' => $type])->andWhere(['login_value' => $value])->select(['user_id'])->one();
        if ($user) {
            return $user['user_id'];
        } else {
            return 0;
        }
    }

    /**
     * 写入登录标识
     * @param int $user_id 用户的user_id
     * @param string $value 
     * @param int $type 登录的类型 1设备 2手机 3QQ 4微博 5微信
     * @return  int 主键
     */
    public static function add($user_id, $value, $type = 1) {
        $model = new UserEmblem();
        $model->create_time = time();
        $model->login_type = $type;
        $model->login_value = $value;
        $model->user_id = $user_id;
        $model->insert();
        return $model->id;
    }

    /**
     * 获取这个用户的全部的登录方式
     * @param  int $user_id
     */
    public static function findAllWay($user_id) {
        $list = self::find()->where(['user_id' => $user_id])->all();
        if (!$list) {
            return FALSE;
        }
        $types = [];
        foreach ($list as $value) {
            $types[] = $value->login_type;
        }
        return $types;
    }
    
    
    /*
     * 返回当前的注册用户总数，不包含设备号登录
     */
    public static function userCount(){
        return  (int)self::find()->where("login_type != :t",[':t'=>1])->count();
    }

}
