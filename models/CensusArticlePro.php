<?php

namespace app\models;

use Yii;
use app\models\CensusArticlePer;

/**
 * This is the model class for table "census_article_pro".
 *
 * @property integer $id
 * @property string $date
 * @property integer $article_id
 * @property integer $pv
 * @property integer $uv
 * @property integer $update_time
 */
class CensusArticlePro extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'census_article_pro';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'article_id', 'update_time'], 'required'],
            [['article_id', 'pv', 'uv', 'update_time'], 'integer'],
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
            'date' => '日期',
            'article_id' => '稿件的id',
            'pv' => '不记用户的总数',
            'uv' => '记用户的总数',
            'update_time' => '更新的时间',
        ];
    }
    
    /**
     * 记录 pv uv
     * @param type $article_id
     */
    public static function addRecord($article_id,$user_id){
        $date = date("Y-m-d");
        //看看有没有
        $model = self::find()->where(['article_id' => $article_id,'date' => $date])
                ->one();
        if (!$model) {
            //初始化
            $model = new CensusArticlePro();
            $model->article_id = $article_id;
            $model->date = $date;
            $model->pv = 0;
            $model->uv = 0;
            $model->update_time = time();
            $model->save();
        }
        //现在的数据
        $today = $model->attributes;
        //数据的更新
        //看看有没有阅读
        if(CensusArticlePer::checkRead($article_id, $user_id)){
            $att=[
                'pv'=>$today['pv']+1,
                'update_time' => time(),
            ];
        }else{
            $att=[
                'uv'=>$today['uv']+1,
                'pv'=>$today['pv']+1,
                'update_time' => time(),
            ];
        }
        return self::updateAll($att, ['id' => $today['id']]);
    }
}
