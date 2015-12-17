<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "census_comments".
 *
 * @property integer $id
 * @property integer $article_id
 * @property string $date
 * @property integer $counts
 * @property integer $all_counts
 * @property integer $update_time
 */
class CensusComments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'census_comments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article_id', 'date', 'counts', 'all_counts', 'update_time'], 'required'],
            [['article_id', 'counts', 'all_counts', 'update_time'], 'integer'],
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
            'article_id' => '稿件id',
            'date' => '日期',
            'counts' => '当天的数量',
            'all_counts' => '总数',
            'update_time' => '更新时间',
        ];
    }
    
    /**
     * 记录每天的弹幕数量 
     * @param  int $article_id 稿件的 id
     */
    public static function addRecord($article_id){
        $date=  date("Y-m-d");
        //获取最早的记录
        $old=self::find()->where(['article_id'=>$article_id])->orderBy(['update_time'=>SORT_DESC])
                ->one();
        if($old){
            $old_attr=$old->attributes;
            $all_contents=$old_attr['all_counts'];
        }else{
            $all_contents=0;
        }
        //看看有没有今天的数据
        $model=self::find()->where(['article_id'=>$article_id,'date'=>$date])
                ->one();
        if(!$model){
            //初始化
            $model=new CensusComments();
            $model->article_id=$article_id;
            $model->date=$date;
            $model->counts=0;
            $model->all_counts=$all_contents;
            $model->update_time=  time();
            $model->save(); 
        }
        //现在的数据
        $today=$model->attributes;
        //数据的更新
        return self::updateAll([
            'counts'=>$today['counts']+1,
            'all_counts'=>$today['all_counts']+1,
            'update_time'=>  time(),
        ], ['id'=>$today['id']]);
    }
}
