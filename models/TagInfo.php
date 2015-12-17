<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tag_info".
 * 标签的数据，脱离于用户的发布内容，完全是标签的基本信息
 *
 * @property integer $id
 * @property string $name
 * @property integer $create_time
 * @property integer $create_uid
 */
class TagInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'create_uid'], 'integer'],
            [['name'], 'string', 'max' => 320]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'name' => '标签的时间',
            'create_time' => '创建的时间',
            'create_uid' => '创建的uid',
        ];
    }
    
    /**
     * 处理标签
     * @param string $name 标签名称
     * @param int $user_id 用户id
     */
    public static function makeTag($name,$user_id){
        //有就返回id,没有就添加一个并返回id
        $info=  self::find()->where(['name'=>$name])->one();
        if($info){
            return $info->id;
        }
        //创建
        $model=new TagInfo();
        $model->name=$name;
        $model->create_time=  time();
        $model->create_uid=$user_id;
        $model->insert();
        return $model->id;
    }
    
    

    /**
     * 看看有没有这个标签
     * @param string $name 标签的名称
     */
    public static function checkByName($name){
        $info=  self::find()->where(['name'=>$name])->one();
        if($info){
            return $info->id;
        }
        return FALSE;
    }

    /**
     * 根据id获取标签的名字
     * @param type $id
     */
    public static function getTagNameById($id){
        $info=  self::find()->where(['id'=>$id])->one();
        if($info){
            return $info->name;
        }
        return FALSE;
    }
    
    /**
     * 根据id获取全部的基本信息
     * @param int $id 商品的id
     */
    public static function getInfoById($id){
        $info=  self::find()->where(['id'=>$id])->one();
        if($info){
            $att= $info->attributes;
            $att['tag_id']=$att['id'];
            $att['tag_name']=$att['name'];
            unset($att['name']);
            unset($att['id']);
            return $att;       
        }
        return FALSE;
    }
}
