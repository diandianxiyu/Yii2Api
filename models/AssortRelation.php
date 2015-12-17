<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "assort_relation".
 *
 * @property integer $id
 * @property integer $item_id
 * @property integer $article_id
 * @property integer $iid
 * @property integer $class_id
 * @property integer $sort
 * @property integer $update_time
 */
class AssortRelation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'assort_relation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['item_id', 'iid', 'class_id', 'update_time'], 'required'],
            [['item_id', 'article_id', 'iid', 'class_id', 'sort', 'update_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'item_id' => '商品主键',
            'article_id' => '商品所在稿件id',
            'iid' => '商品的id标识',
            'class_id' => '所属的分类的id',
            'sort' => '排序字段',
            'update_time' => '更新的时间',
        ];
    }
    
    /**
     * 获取全部相关商品id
     * @param int $class_id
     */
    public static function getItemList($class_id,$count=10,$page=0){
        //获取对应的id
        $list=  self::find()->where(['class_id'=>$class_id])->orderBy(['sort' => SORT_ASC])->limit($count)->offset($count * $page)->all();
        $re=[];
        if($list){
            foreach ($list as $value) {
                $one=$value->attributes;
                $re[]=$one;
            }
        }
        return $re;
    }
    
    /**
     * 获取下一页
     * @param int $class_id
     * @param int $count
     * @param int $page
     * @return type
     */
    public static function getItemListNextPage($class_id,$count=10,$page=0){
        $page++;
        //获取对应的id
        $list=  self::find()->where(['class_id'=>$class_id])->orderBy([ 'sort' => SORT_ASC])->limit($count)->offset($count * $page)->all();
        if($list){
            return TRUE;
        }
        return FALSE;
    }
    

    
    
    
}
