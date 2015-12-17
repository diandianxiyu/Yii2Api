<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_status".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $status_key
 * @property string $status_value
 */
class UserStatus extends \yii\db\ActiveRecord {
    /*
     * 状态，是不是允许别人访问自己的主页，有就是不允许，否则没有
     */

    const STATUS_VISIT_ME = "2";

    /*
     * 状态，禁止发布动态
     */
    const STATUS_PRESS_CONTENT = "3";

    /*
     * 状态，认证状态
     */
    const STATUS_SIGN = "4";

    /*
     * 记数，用户的被点赞的总数
     */
    const COUNTS_LIKE = "5";
    
    /**
     * 状态，首页的推荐顺序
     */
    const STATUS_RECOMMENT_TYPE= "9";
    
    /**
     * 赋值，首页排序，标签相关用户数量
     */
    const VALUE_RECOMMENT_USER="1";
    
    /**
     * 赋值,首页排序，标签点赞的数量
     */
    const  VALUE_RECOMMENT_LIKE="2";

    /*
     * 赋值，首页排序。标签相关内容数量
     */
    const  VALUE_RECOMMENT_CONTENT="3";
    
    /*
     * 状态，内容排序规则
     */
    const STATUS_CONTENT_SORT="10";
    
    /*
     * 赋值，按照创建时间排序
     */
    const VALUE_CONTENT_CREATE_TIME="1";
    
    /*
     *赋值，按照标签更新顺序排序 
     */
    const VALUE_CONTENT_UPDATE_TIME="2";

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user_status';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'status_key', 'status_value'], 'required'],
            [['user_id'], 'integer'],
            [['status_key', 'status_value'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'user_id' => '用户id',
            'status_key' => '类型 1弹幕',
            'status_value' => '值，尽量用负面的，可以被删掉',
        ];
    }

    /**
     * 返回弹幕发送的状态
     * @param int $user_id
     */
    public static function checkCommentStatus($user_id) {
        $model = self::find()->where(['user_id' => $user_id, 'status_key' => "1", 'status_value' => "1"])->one();
        if ($model) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 检查自己是不是设置了禁止别人访问自己的状态
     * @param int $user_id 用户的主键
     */
    public static function checkVisitStatus($user_id) {
        $model = self::find()->where(['user_id' => $user_id, 'status_key' => self::STATUS_VISIT_ME, 'status_value' => "1"])->one();
        if ($model) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    /**
     * 检查自己是不是被禁止发布状态
     * @param int $user_id 用户的主键
     */
    public static function checkPressContentStatus($user_id) {
        $model = self::find()->where(['user_id' => $user_id, 'status_key' => self::STATUS_PRESS_CONTENT, 'status_value' => "1"])->one();
        if ($model) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 检查自己是不是处于认证状态
     * @param int $user_id 用户的主键
     */
    public static function checkSignStatus($user_id) {
        $model = self::find()->where(['user_id' => $user_id, 'status_key' => self::STATUS_SIGN, 'status_value' => "1"])->one();
        if ($model) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取用户的被点赞的总数
     * @param int $user_id 用户的被点赞的总数
     */
    public static function getUserLikeCount($user_id) {
        $model = self::find()->where(['user_id' => $user_id, 'status_key' => self::COUNTS_LIKE])->one();
        if ($model) {
            return (int)$model->status_value;
        } else {
            return 0;
        }
    }
    
    
    
    
    
    /**
     * 添加用户的认证状态
     * @param type $user_id
     */
    public static function ChangeUserSignStatus($user_id){
        $model=new UserStatus();
        $model->user_id=$user_id;
        $model->status_value="1";
        $model->status_key=  self::STATUS_SIGN;
        $model->insert();
        return $model->id;
        
    }
    
    

    /**
     * 添加记数
     * @param int $user_id 用户uid
     */
    public static function likeAddCount($user_id,$count=1){
        //没有就初始化
        $model=  self::likeRefreshStatus($user_id);
        //添加
        $count=(int)$model->status_value + $count;
        $model->status_value =(string)$count;
        $model->save();
    }
    
    /**
     * 减少一个记数
     * @param int $user_id 用户uid
     */
    public static function likeRemoveCount($user_id,$count=1){
        //没有就初始化
        $model=  self::likeRefreshStatus($user_id);
        //添加
        $count=(int)$model->status_value - $count;
        $model->status_value =(string)$count;
        $model->save();
    }
    
    /**
     * 初始化记数
     * @param int $user_id 用户uid
     */
    public static function likeRefreshStatus($user_id) {
        $info=  self::find()->where(['user_id' => $user_id, 'status_key' => self::COUNTS_LIKE])->one();
        if($info){
            return $info;
        }
        //添加
        $model=new UserStatus();
        $model->user_id=$user_id;
        $model->status_value="0";
        $model->status_key=  self::COUNTS_LIKE;
        $model->insert();
        return $model;
    }
    
    /**
     * 获取用户的首页的推荐排序顺序
     * @param int $user_id 用户的uid
     */
    public static function recommentSortGet($user_id){
        //删除之前的
        self::deleteAll(['user_id'=>$user_id,'status_key'=>  self::STATUS_RECOMMENT_TYPE]);
        //变成新的
        $value=[
        self::VALUE_RECOMMENT_USER,  self::VALUE_RECOMMENT_LIKE,  self::VALUE_RECOMMENT_CONTENT,
        ];
        
        $rand= $value[array_rand($value)];
        
        //更新数据
       
         //添加
        $model=new UserStatus();
        $model->user_id=$user_id;
        $model->status_key=  self::STATUS_RECOMMENT_TYPE;
        $model->status_value= $rand;
        $model->insert();
        return $model->status_value;
    }
    
    /**
     * 不需要刷新数据的获取对应的筛选规则
     * @param int $user_id 用户的uid
     */
    public static function recommentSortGetPull($user_id){
        $info=  self::find()->where(['user_id'=>$user_id,'status_key'=>  self::STATUS_RECOMMENT_TYPE])->one();
        return $info->status_value; 
    }
    
    
    /**
     * 获取用户的首页的推荐排序顺序
     * @param int $user_id 用户的uid
     */
    public static function userSortGet($user_id){
        //删除之前的
        self::deleteAll(['user_id'=>$user_id,'status_key'=>  self::STATUS_CONTENT_SORT]);
        //变成新的
        $value=[
        self::VALUE_CONTENT_CREATE_TIME,  self::VALUE_CONTENT_UPDATE_TIME,
        ];
        
        $rand= $value[array_rand($value)];
        
        //更新数据
       
         //添加
        $model=new UserStatus();
        $model->user_id=$user_id;
        $model->status_key=  self::STATUS_CONTENT_SORT;
        $model->status_value= $rand;
        $model->insert();
        return $model->status_value;
    }
    
    /**
     * 不需要刷新数据的获取对应的筛选规则
     * @param int $user_id 用户的uid
     */
    public static function userSortGetPull($user_id){
        $info=  self::find()->where(['user_id'=>$user_id,'status_key'=>  self::STATUS_CONTENT_SORT])->one();
        return $info->status_value; 
    }

}
