<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "census_article_per".
 *
 * @property integer $id
 * @property string $date
 * @property integer $article_id
 * @property integer $user_id
 * @property integer $counts
 * @property integer $update_time
 */
class CensusArticlePer extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'census_article_per';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['date', 'article_id', 'user_id', 'counts', 'update_time'], 'required'],
            [['article_id', 'user_id', 'counts', 'update_time'], 'integer'],
            [['date'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'date' => '日期',
            'article_id' => '稿件id',
            'user_id' => '用户id',
            'counts' => '记数',
            'update_time' => '更新的时间',
        ];
    }

    /**
     * 添加记录到每个用户的阅读记录表
     * @param  int $article_id 稿件 id
     * @param int $user_id 用户的 id
     */
    public function addRecord($article_id, $user_id) {
        $date = date("Y-m-d");
        //看看有没有
        $model = self::find()->where(['article_id' => $article_id, 'user_id' => $user_id, 'date' => $date])
                ->one();
        if (!$model) {
//            return "没有";
            //初始化
            $model = new CensusArticlePer();
            $model->article_id = $article_id;
            $model->user_id = $user_id;
            $model->date = $date;
            $model->counts = 0;
            $model->update_time = time();
            $model->save();
        }
        //现在的数据
        $today = $model->attributes;
        //数据的更新
        return self::updateAll([
                    'counts' => $today['counts'] + 1,
                    'update_time' => time(),
                        ], ['id' => $today['id']]);
    }
    
    /**
     * 检查是不是这个用户今天已经阅读过了，如果没有就记录一次 UV
     * @param int $article_id 稿件 id
     * @param int $user_id 用户 id
     */
    public function checkRead($article_id, $user_id){
        $date = date("Y-m-d");
        //看看有没有
        $model = self::find()->where(['article_id' => $article_id, 'user_id' => $user_id, 'date' => $date])
                ->one();
        if (!$model) {     
            return FALSE;
        }
        return TRUE;
    }

}
