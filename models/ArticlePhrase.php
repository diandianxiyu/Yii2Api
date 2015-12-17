<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_phrase".
 *
 * @property integer $id
 * @property string $text
 * @property integer $sort
 * @property integer $disabled
 * @property integer $update_time
 */
class ArticlePhrase extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article_phrase';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['text', 'sort', 'disabled', 'update_time'], 'required'],
            [['sort', 'disabled', 'update_time'], 'integer'],
            [['text'], 'string', 'max' => 600]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'text' => '文字',
            'sort' => '排序',
            'disabled' => '禁用状态',
            'update_time' => '更新时间',
        ];
    }
    
    /**
     * 获取全部的使用的快捷短语
     */
    public static function getAll(){
        $model=self::find()->where(['disabled'=>0])->select(['id','text'])->orderBy(['sort'=>SORT_DESC])->all();
        if($model){
            return $model;
        }else{
            return [];
        }          
    }
}
