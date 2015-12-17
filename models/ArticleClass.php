<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_class".
 *
 * @property integer $id
 * @property integer $clase_id
 * @property integer $tid
 * @property integer $update_time
 */
class ArticleClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article_class';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['clase_id', 'tid', 'update_time'], 'required'],
            [['clase_id', 'tid', 'update_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'clase_id' => '分类的id',
            'tid' => '稿件的id',
            'update_time' => '更新的时间',
        ];
    }
}
