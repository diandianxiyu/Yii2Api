<?php

namespace app\models;

use Yii;
use app\models\ContentTagList;
use app\components\helper\App;
use app\models\TagInfo;

/**
 * This is the model class for table "content_tag_count".
 * 内容下的标签的数量，标签可以被用户删除，删除之后，不再属于点赞的总数
 * 
 * @property integer $id
 * @property integer $content_id
 * @property integer $tag_id
 * @property integer $counts
 * @property integer $update_time
 */
class ContentTagCount extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'content_tag_count';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['content_id', 'tag_id', 'counts', 'update_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'content_id' => '用户内容id',
            'tag_id' => '标签id',
            'counts' => '数量',
            'update_time' => '更新时间', //0的时候，属于被删除状态
        ];
    }

    /**
     * 添加一个记数
     * @param int $content_id 
     * @param int $tag_id
     */
    public static function addCount($content_id, $tag_id) {
        //没有就初始化
        $model = self::refreshStatus($content_id, $tag_id);
        //添加
        $model->counts +=1;
        $model->update_time = time();
        $model->save();
    }

    /**
     * 减少一个记数
     * @param int $content_id 
     * @param int $tag_id
     */
    public static function removeCount($content_id, $tag_id) {
        //没有就初始化
        $model = self::refreshStatus($content_id, $tag_id);
        //添加
        $model->counts -=1;
        $model->update_time = time();
        $model->save();
    }

    /**
     * 初始化
     * @param int $content_id 内容的id
     * @param int $tag_id 标签的id
     */
    public static function refreshStatus($content_id, $tag_id) {
        $info = self::find()->where(['content_id' => $content_id, 'tag_id' => $tag_id])->one();
        if ($info) {
            return $info;
        }
        //添加
        $model = new ContentTagCount();
        $model->content_id = $content_id;
        $model->update_time = time();
        $model->counts = 0;
        $model->tag_id = $tag_id;
        $model->insert();
        return $model;
    }

    /**
     * 内容下的标签列表
     * @param int $content_id 内容的id
     * @param int $count 每个页面的数量
     * @param int $page 第几页
     */
    public static function getTagList($content_id, $user_id, $count = 5, $page = 0) {
        $list = self::find()->where(['content_id' => $content_id])->limit($count)->offset($count * $page)->orderBy(['counts' => SORT_DESC])->all();
        $tags = [];
        if ($list) {
            //循环
            foreach ($list as $value) {
                //得到id和数量
                $att = $value->attributes;
                $tag = [];
                $tag['tag_id'] = $att['tag_id'];
                $tag['tag_name'] = TagInfo::getTagNameById($att['tag_id']);
                $tag['counts'] = $att['counts'];
                //是不是点赞过
                $tag['liked'] = ContentTagList::checkRelation($content_id, $att['tag_id'], $user_id) ? 1 : 0;
                //返回这个用户的信息
                $tag['user_info'] = App::getUserProfileVer13(ContentTagList::getFirstUserId($content_id, $att['tag_id']));
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    /**
     * 获取单个的标签的信息
     * @param int $content_id 内容信息
     * @param int $tag_id 标签信息
     * @param int $user_id 用户信息
     */
    public static function getSimpleContentTag($content_id, $tag_id, $user_id) {
        $value = self::find()->where(['content_id' => $content_id, 'tag_id' => $tag_id])->limit(1)->one();
        $att = $value->attributes;
        $tag = [];
        $tag['tag_id'] = $att['tag_id'];
        $tag['tag_name'] = TagInfo::getTagNameById($att['tag_id']);
        $tag['counts'] = $att['counts'];
        //是不是点赞过
        $tag['liked'] = ContentTagList::checkRelation($content_id, $att['tag_id'], $user_id) ? 1 : 0;
        //返回这个用户的信息
        $tag['user_info'] = App::getUserProfileVer13(ContentTagList::getFirstUserId($content_id, $att['tag_id']));
        return $tag;
    }

    /**
     * 内容下的标签列表
     * @param int $content_id 内容的id
     * @param int $count 每个页面的数量
     * @param int $page 第几页
     */
    public static function getTagListNextPage($content_id, $user_id, $count = 5, $page = 0) {
        $page++;
        $list = self::find()->where(['content_id' => $content_id])->limit($count)->offset($count * $page)->orderBy(['counts' => SORT_DESC])->all();
        $tags = [];
        if ($list) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * 获取标签列表
     * @param type $num
     */
    public static function getHostTags($count = 5, $page = 0) {
        $offset = $count * $page;
        $connection = Yii::$app->db; //连接
        $sql = "SELECT SUM(`counts`) as tag_count ,tag_id,MAX(update_time) as update_time FROM `content_tag_count` WHERE `update_time` != 0 GROUP BY `tag_id` ORDER by tag_count desc LIMIT {$count} OFFSET {$offset}";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
//        return $result;

        $list = [];
        if (count($result)) {
            //加入
            foreach ($result as $value) {
                $list[] = App::getTagInfo($value['tag_id']);
            }
        }

        return $list;
    }

    /**
     * 获取标签列表是否有下一页
     * @param type $num
     */
    public static function getHostTagsNextPage($count = 5, $page = 0) {
        $page++;
        $offset = $count * $page;
        $connection = Yii::$app->db; //连接
        $sql = "SELECT SUM(`counts`) as tag_count ,tag_id,MAX(update_time) as update_time FROM `content_tag_count` WHERE `update_time` != 0 GROUP BY `tag_id` ORDER by tag_count desc LIMIT {$count} OFFSET {$offset}";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * 根据标签id获取总数和内容的数量
     * @param int $tag_id 标签id
     */
    public static function getTInfoByTagId($tag_id) {
        $sql = "SELECT SUM(`counts`) as like_count ,MAX(update_time) as update_time ,COUNT(`content_id`) as content_count FROM `content_tag_count` WHERE `tag_id` = {$tag_id}";
        $connection = Yii::$app->db; //连接
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        return $result[0];
    }
    
    /**
     * 检查这个有没有，如果有，就返回这个标签总共有多少人进行了点赞 
     */
    public static function checkTagExist($count_id,$tag_id){
        $info=  self::find()->where(['content_id'=>$count_id,'tag_id'=>$tag_id])->one();
        if($info){
            return $info->counts;
        }
        return FALSE;
    }
    
    /**
     * 删除相关数据
     */
    public static function delTagByContentId($count_id,$tag_id){
        $info=  self::deleteAll(['content_id'=>$count_id,'tag_id'=>$tag_id]);
        return $info;
    }
    
    /**
     * 获取这个内容下的全部的签到数量
     * @param int $content_id 内容的id
     */
    public static function getLikeCountByContent($content_id){
        $sql="SELECT `content_id`, SUM(`counts`) as all_counts FROM `content_tag_count` WHERE `content_id` = {$content_id} GROUP BY `content_id`";
        $connection = Yii::$app->db; //连接
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        if(count($result) == 0){
            return 0;
        }
        return $result[0]['all_counts'];
    }
    
    
    /**
     * 删除相关数据
     */
    public static function delAllByContentId($content_id){
        $info=  self::deleteAll(['content_id'=>$content_id]);
        return $info;
    }
    

}
