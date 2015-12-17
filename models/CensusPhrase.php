<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "census_phrase".
 *
 * @property integer $id
 * @property integer $article_id
 * @property integer $phrase_id
 * @property integer $counts
 * @property integer $update_time
 * @property string $date
 */
class CensusPhrase extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'census_phrase';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article_id', 'phrase_id', 'update_time', 'date'], 'required'],
            [['article_id', 'phrase_id', 'counts', 'update_time'], 'integer'],
            [['date'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'article_id' => '稿件 id',
            'phrase_id' => '快捷短语 id',
            'counts' => '数量',
            'update_time' => '最后的更新时间',
            'date' => '日期，Y-m-d',
        ];
    }
    
    /**
     * 增加快捷短语的使用记录
     * @param  int $article_id 稿件的 id
     * @param int $phrase_id 快捷短语的 id
     * @return bool 是是不是更新成功
     */
    public static function addRecord($article_id,$phrase_id){
        $date=  date("Y-m-d");
        //看看有没有
        $model=self::find()->where(['article_id'=>$article_id,'phrase_id'=>$phrase_id,'date'=>$date])
                ->one();
        if(!$model){
            //初始化
            $model=new CensusPhrase();
            $model->article_id=$article_id;
            $model->phrase_id=$phrase_id;
            $model->date=$date;
            $model->counts=0;
            $model->update_time=  time();
            $model->save(); 
        }
        //现在的数据
        $today=$model->attributes;
        //数据的更新
        return self::updateAll([
            'counts'=>$today['counts']+1,
            'update_time'=>  time(),
        ], ['id'=>$today['id']]);
    }
}
