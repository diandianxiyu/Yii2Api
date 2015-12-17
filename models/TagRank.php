<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tag_rank".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $tag_id
 * @property integer $create_time
 * @property integer $serach_count
 * @property integer $link_tag_count
 * @property integer $link_tag_update_time
 * @property integer $add_tag_count
 * @property integer $add_tag_update_time
 * @property integer $add_content_count
 * @property integer $add_content_update_time
 * @property integer $rank
 * @property integer $rank_update_time
 */
class TagRank extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_rank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'tag_id', 'create_time', 'serach_count', 'link_tag_count', 'link_tag_update_time', 'add_tag_count', 'add_tag_update_time', 'add_content_count', 'add_content_update_time', 'rank', 'rank_update_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'user_id' => '用户',
            'tag_id' => '标签',
            'create_time' => '第一次接触的时间,包含对应添加标签，点赞标签',
            'serach_count' => '搜索的次数',
            'link_tag_count' => '点赞的标签的名字',
            'link_tag_update_time' => '最后点赞的标签的时间',
            'add_tag_count' => '创建的标签的个数',
            'add_tag_update_time' => '给内容添加标签的最后的时间',
            'add_content_count' => '添加的内容的个数',
            'add_content_update_time' => '添加相关内容的最后更新时间',
            'rank' => '最终计算出来的数值，每次进行更新',
            'rank_update_time' => '最终评分的时间',
        ];
    }
    
    /**
     * 初始化用户标签的数据
     * @param int $user_id 用户uid
     * @param int $tag_id 标签的id
     */
    public static function initRank($user_id,$tag_id){
        //看有没有
        $info=  self::find()->where(['user_id'=>$user_id,'tag_id'=>$tag_id])->one();
        if($info){
            return $info;
        }
        $model=new TagRank();
        $model->user_id=$user_id;
        $model->tag_id=$tag_id;
        $model->create_time=  time();
        $model->serach_count=0;
        $model->link_tag_count=0;
        $model->link_tag_update_time=0;
        $model->add_tag_count=0;
        $model->add_tag_update_time=0;
        $model->add_content_count=0;
        $model->add_content_update_time=0;
        $model->rank=0;
        $model->rank_update_time=0;
        $model->insert();
        return $model;
    }
    
    
    /**
     * 计算用户和标签的对应的评分
     * @param int $id 主键
     */
    public static function makeMark($id){
        $info=  self::find()->where(['id'=>$id])->one();   
        //计算 用户的标签的权重值 = 添加内容的 * 3 + 添加标签 * 2 + 点赞标签 * 1     
        $mark=($info->add_content_count * 3) + ($info->add_tag_count * 2) + ($info->add_content_count );
        //执行更新的操作
        self::updateAll(['rank'=>$mark,'rank_update_time'=>  time()], ['id'=>$id]);
    }
    
    /**
     * 添加标签
     * @param int $user_id 用户user_id
     * @param int $tag_id 标签id
     */
    public static function addTag($user_id,$tag_id){
        //增加数量和更新时间
        $model=  self::initRank($user_id, $tag_id);
        
        //更新数字
        $model->add_tag_count +=1;
        $model->add_tag_update_time=  time();
        $model->save();
        
        self::makeMark($model->id);
    }
    
    /**
     * 添加内容
     * @param int $user_id 用户的id
     * @param int $tag_id 标签的id
     */
    public static function addContent($user_id,$tag_id){
        $model=  self::initRank($user_id, $tag_id);
        
        //更新数字
        $model->add_content_count +=1;
        $model->add_content_update_time=  time();
        $model->save();
        
        self::makeMark($model->id);
    }
    
    /**
     * 用户的相关信息
     * @param int $user_id
     * @param int $tag_id
     */
    public static function linkTag($user_id,$tag_id){
        $model=  self::initRank($user_id, $tag_id);
        
        //更新数字
        $model->link_tag_count +=1;
        $model->link_tag_update_time=  time();
        $model->save();
        
        self::makeMark($model->id);
    }
    
    /**
     * 获取相关的标签的数量
     * @param int $user_id 用户主键
     */
    public static function getTagNum($user_id){
        $num=  self::find()->where(['user_id'=>$user_id])->count();
        return $num;
    }
    
    /**
     * 获取当前用户的活跃的若干个内容
     * @param type $user_id
     */
    public static function getList($user_id){
        $list=  self::find()->select(['tag_id'])->where(['user_id'=>$user_id])->orderBy(['rank'=>SORT_DESC])->limit(20)->all();
        $ids=[];
        if($list){
            foreach ($list as $value) {
                $ids[]=$value->tag_id;
            }
        }
        return $ids;
    }
}
