<?php

namespace app\models;

use Yii;
use app\models\UserEmblem;

/**
 * This is the model class for table "census_app".
 *
 * @property integer $id
 * @property string $date
 * @property integer $counts
 * @property integer $active_counts
 * @property integer $all_counts
 * @property integer $update_time
 */
class CensusApp extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'census_app';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['counts', 'active_counts', 'all_counts', 'update_time'], 'integer'],
            [['date'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'date' => '日期',
            'counts' => '数量',
            'active_counts' => '活跃用户总数',
            'all_counts' => '总数',
            'update_time' => '更新时间',
        ];
    }

    /**
     * 检查当前有没有用户新增
     */
    public static function check() {
        $date = date("Y-m-d");
        $info = self::find()->where(['date' => $date])->one();
        if (!$info) {
            $model = new CensusApp();
            $model->date = time();
            $model->active_counts = 0;
            $model->all_counts = 0;
            $model->counts = 0;
            $model->update_time = time();
            $model->insert();
        }
    }

    /**
     * 初始化数据，写入今天的数据
     */
    public static function data_init() {
        $date = date("Y-m-d");
        //用户总数 login_type不是1的，都属于登录用户
        $all_counts = UserEmblem::userCount();
        
        //return $all_counts;
        $model = new CensusApp();
        $model->date = $date;
        $model->active_counts = 0;
        $model->all_counts = $all_counts;
        $model->counts = 0;
        $model->update_time = time();
        $model->insert();
        return $all_counts;
    }

    /**
     * 增加一个用户
     */
    public static function addUser() {
         $date=  date("Y-m-d");
        //看看今天有没有数据
        $info_today = self::find()->where(['date' => $date])->one();
        if (!$info_today) {
            //查找之前的数据,
            $info = self::find()->select(['all_counts'])->orderBy(['update_time' => SORT_DESC])->one();
           
            if (!$info) {
                //没有数据，统计出数据
                $all_counts = self::data_init();
            } else {
                //存在数据，对数据进行读取
                $all_counts = $info['all_counts'];
            }
            $counts = 0;
            $active_counts = 0;
            
            //创建今天的     
            $model=new CensusApp();
            $model->active_counts=$active_counts;
            $model->all_counts=$all_counts;
            $model->counts=$counts;
            $model->date=$date;
            $model->update_time=time();
            $model->insert();
            
        } else {
            //找出今天总数
            $all_counts = $info_today->all_counts;
            $counts = $info_today->counts;
            $active_counts = $info_today->active_counts; 
          
        }

        $att = [
            'counts' => $counts + 1,
            'active_counts' => $active_counts,
            'all_counts' => $all_counts + 1,
            'update_time' => time()
        ];

        return  self::updateAll($att, ['date' => $date]);
    }

    /**
     * 活跃用户 +1 ,通过首页的api的增加pv进行判断
     */
    public static function addActiveUser() {
         $date=  date("Y-m-d");
        //看看今天有没有数据
        $info_today = self::find()->where(['date' => $date])->one();
        
        if (!$info_today) {
            
            //查找之前的数据
            $info = self::find()->select(['all_counts'])->orderBy(['update_time' => SORT_DESC])->one();          
            if (!$info) {
                //没有数据，统计出数据
                $all_counts = self::data_init();
            } else {
                //存在数据，对数据进行读取
                $all_counts = $info->all_counts;
            }
            $counts = 0;
            $active_counts = 0;
            
            
            //创建今天的
            
            $model=new CensusApp();
            $model->active_counts=$active_counts;
            $model->all_counts=$all_counts;
            $model->counts=$counts;
            $model->date=$date;
            $model->update_time=time();
            $model->insert();
        } else {     
            //找出今天总数
            $all_counts = $info_today->all_counts;
            $counts = $info_today->counts;
            $active_counts = $info_today->active_counts;

        }

        //更新今天的数据
        $att = [
            'counts' => $counts,
            'active_counts' => $active_counts + 1,
            'all_counts' => $all_counts,
            'update_time' => time()
        ];

        return    self::updateAll($att, ['date' => $date]);
    }

}
