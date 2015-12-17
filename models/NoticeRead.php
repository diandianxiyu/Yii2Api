<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notice_read".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $update_time
 */
class NoticeRead extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'notice_read';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'update_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'user_id' => '用户user_id',
            'update_time' => '阅读的时间',
        ];
    }

    /**
     * 创建一个
     * @param type $user_id
     */
    public static function readMark($user_id) {

        self::deleteAll(['user_id' => $user_id]);

        $model = new NoticeRead();
        $model->user_id = $user_id;
        $model->update_time = time();
        $model->insert();
        return $model->id;
    }

    /**
     * 获取最新一次
     * @param type $user_id
     */
    public static function getMark($user_id) {
        $info = self::find()->where(['user_id' => $user_id])->one();
        if ($info) {
            return $info->update_time;
        }
        return 0;
    }

}
