<?php

namespace app\models;

use Yii;
use app\models\CensusApi;
use app\models\CensusApp;


/**
 * This is the model class for table "census_api_pro".
 *
 * @property integer $id
 * @property string $date
 * @property string $api_id
 * @property integer $pv
 * @property integer $uv
 */
class CensusApiPro extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'census_api_pro';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date', 'api_id', 'uv'], 'required'],
            [['pv', 'uv'], 'integer'],
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
            'api_id' => '接口的id',
            'pv' => '访问的总数',
            'uv' => '访问的总人数',
        ];
    }
    
    /**
     * 记录 pv uv
     * @param type $article_id
     */
    public static function addRecord($user_id,$api_id){
        $date = date("Y-m-d");
        //看看有没有
        $model = self::find()->where(['api_id' => $api_id,'date' => $date])
                ->one(); 
        if (!$model) {
            //初始化
            $model = new CensusApiPro();
            $model->api_id = $api_id;
            $model->date = $date;
            $model->pv = 0;
            $model->uv = 0;
            $model->insert();
        }
        //现在的数据
        $today = $model->attributes;
        //数据的更新
        //看看有没有阅读
        if(CensusApi::check($user_id,$api_id)){
            $att=[
                'pv'=>$today['pv']+1,
                
            ];
        }else{
            $att=[
                'uv'=>$today['uv']+1,
                'pv'=>$today['pv']+1,
                
            ];
            
            if($api_id == 'v1/presses/pressList'){
               //增加用户的UV
               CensusApp::addActiveUser();
            }
        }
        return self::updateAll($att, ['id' => $today['id']]);
    }
    
}
