<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_goods".
 *
 * @property integer $id
 * @property string $title
 * @property string $price
 * @property string $discount_price
 * @property string $open_iid
 * @property string $pic_url
 * @property string $pics
 * @property string $properties_and_values
 * @property string $shop_type
 * @property integer $update_time
 * @property string $tag_id
 */
class ArticleGoods extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pics'], 'string'],
            [['update_time'], 'integer'],
            [['title', 'price', 'properties_and_values'], 'string', 'max' => 800],
            [['discount_price', 'tag_id'], 'string', 'max' => 32],
            [['open_iid'], 'string', 'max' => 24],
            [['pic_url'], 'string', 'max' => 360],
            [['shop_type'], 'string', 'max' => 11]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'title' => '商品名称',
            'price' => '价格',
            'discount_price' => '促销的价格',
            'open_iid' => '商品的 iid',
            'pic_url' => '图片地址',
            'pics' => '商品图',
            'properties_and_values' => '商品描述',
            'shop_type' => '店铺类型',
            'update_time' => '商品更新时间',
            'tag_id' => '收藏夹 id',
        ];
    }
    
    /**
     * 获取商品详细信息 
     * @param  int $open_iid 
     */
    public static function getInfoById($open_iid){
        $info=  self::find()->where(['open_iid'=>$open_iid])->one()->attributes;
        return $info;
    }
    
    /**
     * 获取类型
     */
    public static function getType($open_iid){
        $info=  self::find()->where(['open_iid'=>$open_iid])->one();
        if($info){
            return $info->shop_type;
        }else{
            return FALSE;
        }
    }
}
