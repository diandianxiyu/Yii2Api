<?php

namespace app\models;

use Yii;
use app\models\CensusApiPro;
/**
 * This is the model class for table "census_api".
 *
 * @property integer $id
 * @property string $date
 * @property integer $user_id
 * @property string $api_id
 * @property integer $counts
 */
class CensusApi extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'census_api';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'user_id', 'api_id'], 'required'],
            [['user_id', 'counts'], 'integer'],
            [['date', 'api_id'], 'string', 'max' => 32]
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
            'user_id' => '用户的id',
            'api_id' => '接口的id',
            'counts' => '被访问的次数',
        ];
    }
    
    /**
     * 增加接口访问次数
     * @param int $user_id 用户的id
     * @param string $api_id 接口名字
     */
    public static function add($user_id,$api_id){  
        CensusApiPro::addRecord($user_id, $api_id);
        //看看有没有
        $date=  date("Y-m-d");
        $is_exist=self::find()->where(['date'=>$date])
                ->andWhere(['user_id'=>$user_id])
                ->andWhere(['api_id'=>$api_id])
                ->select(['id','counts'])
                ->one();
        if($is_exist){
            //进行更新
            self::updateAll([
                'counts'=>$is_exist['counts'] +1,
            ],['id'=>$is_exist['id']]);
        }else{
            //进行写入
            $model=new CensusApi();
            $model->date=$date;
            $model->counts=1;
            $model->user_id=$user_id;
            $model->api_id=$api_id;
            $model->insert();
        }
    }
    
    
     /**
     * 检查是不是这个用户今天已经阅读过了，如果没有就记录一次 UV
     * @param int $user_id 用户
     * @param int $api_id 接口
     */
    public function check($user_id,$api_id){
        $date = date("Y-m-d");
        //看看有没有
        $model = CensusApi::find()->where(['api_id' => $api_id, 'user_id' => $user_id, 'date' => $date])
                ->one();
        if (!$model) {     
            return FALSE;
        }
        return TRUE;
    }
}
