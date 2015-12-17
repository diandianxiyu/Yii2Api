<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_profile".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $nikename
 * @property integer $gender
 * @property integer $update_time
 */
class UserProfile extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user_profile';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'gender', 'update_time'], 'integer'],
            [['nikename'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'user_id' => '用户的 id',
            'nikename' => '用户昵称',
            'gender' => '用户性别 性别 1男 2女 3未知',
            'update_time' => '更新的时间',
        ];
    }

    /**
     * 添加对应
     * @param int $user_id 用户的 id
     * @param string $nikename 用户昵称
     * @param int $gender 性别 1男 2 女
     */
    public static function add($user_id, $nikename, $gender) {
        $model = new UserProfile();
        $model->gender = $gender;
        $model->nikename = $nikename;
        $model->user_id = $user_id;
        $model->update_time = time();
        $model->insert();
        return $model;
    }

    /**
     * 返回基本信息
     * @param  int $user_id 用户的id
     */
    public static function getProfile($user_id) {
        $info = self::find()->where(['user_id' => $user_id])->one();
        if ($info) {
            $att = $info->attributes;
            unset($att['user_id']);
            unset($att['id']);
            unset($att['update_time']);
            return $att;
        } else {
            return FALSE;
        }
    }

    /**
     * 修改性别
     * @param  int $user_id
     * @param int $gender
     */
    public static function editGender($user_id, $gender) {
        $update = self::updateAll([
                    'gender' => $gender,
                    'update_time' => time(),
                        ], ['user_id' => $user_id]);
        return $update;
    }
    
    /**
     * 修改用户昵称
     * @param int $user_id
     * @param string $nikename
     */
    public static function editNikename($user_id,$nikename){
        $update = self::updateAll([
                    'nikename' => $nikename,
                    'update_time' => time(),
                        ], ['user_id' => $user_id]);
        return $update;
    }
    

}
