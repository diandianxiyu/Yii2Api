<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_records".
 *
 * @property integer $id
 * @property integer $article_id
 * @property integer $manager_id
 * @property string $action
 * @property integer $update_time
 */
class ArticleRecords extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article_records';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article_id', 'manager_id', 'action', 'update_time'], 'required'],
            [['article_id', 'manager_id', 'update_time'], 'integer'],
            [['action'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'article_id' => '稿件的id',
            'manager_id' => '管理员的id',
            'action' => '对应的操作',
            'update_time' => '更新时间',
        ];
    }
}
