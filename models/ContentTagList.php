<?php

namespace app\models;

use Yii;
use app\models\TagRank;

/**
 * This is the model class for table "content_tag_list".
 *
 * @property integer $id
 * @property integer $content_id
 * @property integer $tag_id
 * @property integer $user_id
 * @property integer $create_time
 * @property integer $disbaled
 * @property integer $status
 * @property integer $sort
 */
class ContentTagList extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'content_tag_list';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['content_id', 'tag_id', 'user_id', 'create_time', 'disbaled', 'status', 'sort'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'content_id' => '创建内容的id',
            'tag_id' => '标签的id',
            'user_id' => '添加这个标签的用户的id',
            'create_time' => '添加标签的时间',
            'disbaled' => '是否被禁用，默认0',
            'status' => '状态，保留字段，默认1',
            'sort' => '排序，保留字段，默认100',
        ];
    }

    /**
     * 创建对应的关系
     * @param int $content_id 内容的id
     * @param int $tag_id 标签的id
     * @param int $user_id 用户的id
     */
    public static function addRelation($content_id, $tag_id, $user_id) {
        //判断有没有
        $check = self::checkRelation($content_id, $tag_id, $user_id);
        if ($check == FALSE) {
            $model = new ContentTagList();
            $model->content_id = $content_id;
            $model->create_time = time();
            $model->disbaled = 0;
            $model->sort = 100;
            $model->status = 1;
            $model->tag_id = $tag_id;
            $model->user_id = $user_id;
            $model->insert();
            return $model->id;
        }
        return $check;
    }

    /**
     * 检查是不是已经点赞 
     * @param int $content_id 内容的id
     * @param int $tag_id 标签的id
     * @param int $user_id 用户的id
     */
    public static function checkRelation($content_id, $tag_id, $user_id) {
        $info = self::find()->where(['content_id' => $content_id, 'tag_id' => $tag_id, 'user_id' => $user_id, 'disbaled' => 0])->one();
        if ($info) {
            return $info->id;
        }
        return FALSE;
    }

    /**
     * 获取第一个给这个内容的这个标签的用户
     * @param int $content_id 内容的id
     * @param int $tag_id 标签的id
     */
    public static function getFirstUserId($content_id, $tag_id) {
        $info = self::find()->where(['content_id' => $content_id, 'tag_id' => $tag_id, 'disbaled' => 0])->orderBy(['create_time' => SORT_ASC])->one();
        if ($info) {
            return $info->user_id;
        }
        return FALSE;
    }

    /**
     * 删除对应的关系
     * @param int $content_id
     * @param int $tag_id
     */
    public static function delRelation($content_id, $tag_id) {
        return self::updateAll(['disbaled' => 1], ['content_id' => $content_id, 'tag_id' => $tag_id, 'disbaled' => 0]);
    }

    /**
     * 删除内容下的全部的标签下的关系
     * @param int $content_id
     * @param int $tag_id
     */
    public static function delAllRelationByContentId($content_id) {
        return self::updateAll(['disbaled' => 1], ['content_id' => $content_id, 'disbaled' => 0]);
    }

    /**
     * 判断是不是已经通过任何一个内容进行了标签和用户的关联
     * @param int $tag_id 标签的id
     * @param int $user_id 用户的id
     */
    public static function checkUserTagRelation($tag_id, $user_id) {
        $info = self::find()->where(['tag_id' => $tag_id, 'user_id' => $user_id, 'disbaled' => 0])->one();
        if ($info) {
            return $info->id;
        }
        return FALSE;
    }

    /**
     * 获取列表
     */
    public static function getRecommentList($c_type, $tag_ids,$page = 0, $count = 20, $time = 0) {
        $ids_str=  implode(",", $tag_ids);
        $offset=$page * $count;
        $sql = "SELECT   `content_base`.`content_cid`  FROM `content_tag_list`  ";
        $sql.=" LEFT JOIN `content_base`";
        $sql.=" ON `content_tag_list`.`content_id` = `content_base`.`id` ";
        $sql.=" WHERE `content_tag_list`.`disbaled` = 0 AND `content_base`.`status` = 1 AND `content_base`.`create_time` < {$time}  AND   `content_tag_list`.`tag_id`  IN ({$ids_str})";
        $sql.=" GROUP BY `content_base`.`id` ";

        switch ($c_type) {
            case UserStatus::VALUE_CONTENT_CREATE_TIME:
                $sql.=" ORDER BY `content_base`.`create_time`  DESC ";

                break;
            case UserStatus::VALUE_CONTENT_UPDATE_TIME:
                $sql.=" ORDER BY `content_base`.`update_time`  DESC ";

                break;
            default:
                $sql.=" ORDER BY `content_base`.`update_time`  DESC ";
                break;
        }

        $sql.=" LIMIT {$count} OFFSET {$offset}";
        //执行语句
        $connection = Yii::$app->db; //连接
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        return $result;
    }
    
    
    /**
     * 获取列表检查是不是还有下一页
     */
    public static function getRecommentListNextPage($c_type, $tag_ids,$page = 0, $count = 20, $time = 0) {
        $page++;
        $ids_str=  implode(",", $tag_ids);
        $offset=$page * $count;
        $sql = "SELECT   `content_base`.`content_cid`  FROM `content_tag_list`  ";
        $sql.=" LEFT JOIN `content_base`";
        $sql.=" ON `content_tag_list`.`content_id` = `content_base`.`id` ";
        $sql.=" WHERE `content_tag_list`.`disbaled` = 0 AND `content_base`.`status` = 1 AND `content_base`.`create_time` < {$time}  AND   `content_tag_list`.`tag_id`  IN ({$ids_str})";
        $sql.=" GROUP BY `content_base`.`id` ";

        switch ($c_type) {
            case UserStatus::VALUE_CONTENT_CREATE_TIME:
                $sql.=" ORDER BY `content_base`.`create_time`  DESC ";

                break;
            case UserStatus::VALUE_CONTENT_UPDATE_TIME:
                $sql.=" ORDER BY `content_base`.`update_time`  DESC ";

                break;
            default:
                $sql.=" ORDER BY `content_base`.`update_time`  DESC ";
                break;
        }

        $sql.=" LIMIT {$count} OFFSET {$offset}";
        //执行语句
        $connection = Yii::$app->db; //连接
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        return count($result);
    }

}
