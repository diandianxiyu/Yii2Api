<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_class_info".
 *
 * @property integer $id
 * @property string $name
 * @property integer $update_time
 */
class ArticleClassInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article_class_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'update_time'], 'required'],
            [['update_time'], 'integer'],
            [['name'], 'string', 'max' => 300]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'name' => '名称',
            'update_time' => '更新时间',
        ];
    }
}
