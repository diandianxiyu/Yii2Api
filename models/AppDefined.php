<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_defined".
 *
 * @property integer $id
 * @property integer $function_id
 * @property string $value
 * @property string $remark
 */
class AppDefined extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app_defined';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['function_id', 'value', 'remark'], 'required'],
            [['function_id'], 'integer'],
            [['value', 'remark'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'function_id' => '功能id',
            'value' => '功能值',
            'remark' => '备注',
        ];
    }
    
    /**
     * 随机获取头像
     */
    public static function getAvatar(){
        $avatar_list=self::find()->where(['like','function_id','avatar'])->all();
        
        $arr=[];
        foreach ($avatar_list as $value) {
            $att=$value->attributes;
            $arr[]=$att['value'];
        }

        $av= $arr[array_rand($arr)];
        //替换域名
        //$av_cdn=  str_replace(Yii::$app->params['img_url'], Yii::$app->params['img_cdn'], $av);

        return $av;
    }
}
