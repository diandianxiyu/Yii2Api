<?php

namespace app\models;

use Yii;
use app\models\ArticleSection;

/**
 * This is the model class for table "article_focus".
 *
 * @property integer $id
 * @property integer $article_id
 * @property integer $tid
 * @property string $focus_map
 * @property integer $sort
 * @property integer $disable
 */
class ArticleFocus extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'article_focus';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['article_id', 'tid', 'sort', 'disable'], 'integer'],
            [['focus_map'], 'string', 'max' => 300]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'article_id' => '稿件的主键id',
            'tid' => '稿件的tid',
            'focus_map' => '焦点图',
            'sort' => '排序，默认100',
            'disable' => '禁用状态，默认禁用',
        ];
    }

    /**
     * 按排序倒序获取全部的列表
     */
    public static function getList() {
        $list = self::find()->where("disable = :dis", [':dis' => 0])->orderBy(['sort' => SORT_ASC])->all();
        if ($list) {
            return $list;
        } else {
            return [];
        }
    }

    /**
     * 根据tid获取对应的封面图
     * @param int $tid 封面图
     */
    public static function getFocusMapByTid($tid) {
        $info=  self::find()->where(['tid'=>$tid])->one();
        return $info->focus_map;
    }

}
