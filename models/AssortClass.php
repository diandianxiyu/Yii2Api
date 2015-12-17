<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "assort_class".
 *
 * @property integer $id
 * @property string $name
 * @property integer $pid
 * @property integer $status
 * @property integer $update_time
 * @property integer $disable
 * @property integer $sort
 */
class AssortClass extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'assort_class';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'update_time'], 'required'],
            [['pid', 'status', 'update_time', 'disable', 'sort'], 'integer'],
            [['name'], 'string', 'max' => 600]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'name' => '标签的名称',
            'pid' => '上一级id',
            'status' => '',
            'update_time' => '更新时间',
            'disable' => '禁用状态，1 禁用，0 启用',
            'sort' => '排序',
        ];
    }
    
    /**
     * 获取全部的推荐到首页的分类
     */
    public static function getTopClass(){
        $list=  self::find()->where(['status'=>1])->orderBy(['sort'=>SORT_DESC])->all();
        $re=[];
        if($list){
            foreach ($list as $value) {
                $one=[];
                $one['class_id']=$value->id;
                $one['name']=$value->name;
                $re[]=$one;
            }
        }
        return $re;
    }
    
    
    /**
     * 判断是不是已经上线 
     * @param int $id
     */
    public static function checkExist($id){
        $info=  self::find()->where(['id'=>$id])->one();
        
        if($info){
            return TRUE;
        }
        return FALSE;
    }
}
