<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "number_order".
 *
 * @property integer $id
 * @property integer $type
 * @property integer $lastest
 * @property integer $update_time
 */
class NumberOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'number_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'lastest', 'update_time'], 'required'],
            [['type', 'lastest', 'update_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'type' => '类型',
            'lastest' => '最新的id',
            'update_time' => '更新的时间',
        ];
    }
    
    /**
     * 获取最新的序列id,1用户、2稿件
     */
    public static function getNewid($type = 1){
        //默认是5位的
        //检查有没有
        $ids=self::find()->where(['type'=>$type])->one();
        if(!$ids){
            //进行初始化
            $model= new NumberOrder();
            $model->lastest=1000;
            $model->type=$type;
            $model->update_time=  time();
            $model->insert();
            return $model->lastest;
        }
        //进行累加
        self::updateAll([
            'lastest'=>$ids['lastest']+1,
            'update_time'=>  time(),
        ], ['id'=>$ids['id']]);
        return $ids['lastest']+1;
    }
}
