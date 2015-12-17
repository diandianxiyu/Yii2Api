<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_splash".
 *
 * @property integer $id
 * @property string $name
 * @property string $content
 * @property string $url
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $disabled
 * @property integer $update_time
 * @property integer $manager
 */
class AppSplash extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app_splash';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'url', 'start_time', 'end_time', 'update_time', 'manager'], 'required'],
            [['content', 'url'], 'string'],
            [['start_time', 'end_time', 'disabled', 'update_time', 'manager'], 'integer'],
            [['name'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'name' => '闪屏名称',
            'content' => '闪屏简介',
            'url' => '闪屏图url',
            'start_time' => '开始时间',
            'end_time' => '结束时间',
            'disabled' => '禁启用，1禁0启，默认1',
            'update_time' => '更新时间',
            'manager' => '管理员',
        ];
    }
}
