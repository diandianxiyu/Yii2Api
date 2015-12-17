<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "census_share".
 *
 * @property integer $id
 * @property integer $article_id
 * @property string $date
 * @property integer $share_type
 * @property integer $counts
 */
class CensusShare extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'census_share';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['article_id', 'date', 'share_type'], 'required'],
            [['article_id', 'share_type', 'counts'], 'integer'],
            [['date'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'article_id' => '稿件id',
            'date' => '日期',
            'share_type' => '分享类型，朋友圈1，微信好友2，QQ空间3，QQ好友4，新浪微博5',
            'counts' => '记数',
        ];
    }

    /**
     * 添加分享记数
     * @param  int $article_id 稿件 id
     * @param int $share_type 分享的类型 朋友圈1，微信好友2，QQ空间3，QQ好友4，新浪微博5
     */
    public static function addRecord($article_id, $share_type = 1) {
        $date = date("Y-m-d");
        //看看有没有
        $model = self::find()->where(['article_id' => $article_id, 'share_type' => $share_type, 'date' => $date])
                ->one();
        if (!$model) {
            //初始化
            $model = new CensusShare();
            $model->article_id = $article_id;
            $model->share_type=$share_type;
            $model->date = $date;
            $model->counts = 0;
            $model->save();
        }
        //现在的数据
        $today = $model->attributes;
        //数据的更新
        return self::updateAll([
                    'counts' => $today['counts'] + 1,
                        ], ['id' => $today['id']]);
    }

}
