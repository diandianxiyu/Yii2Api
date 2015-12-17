<?php

namespace app\models;

use Yii;
use app\models\ContentTagList;
use app\models\UserStatus;

/**
 * This is the model class for table "tag_record".
 *
 * @property integer $id
 * @property integer $tag_id
 * @property integer $link_content_count
 * @property integer $like_count
 * @property integer $user_count
 * @property integer $update_time
 */
class TagRecord extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'tag_record';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['tag_id', 'link_content_count', 'like_count', 'user_count', 'update_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'tag_id' => '标签的id',
            'link_content_count' => '关联的标签的数量',
            'like_count' => '点赞的数量',
            'user_count' => '用户的数量',
            'update_time' => '更新的时间',
        ];
    }

    /**
     * 初始化数据
     * @param int $tag_id 标签id
     */
    public static function initData($tag_id) {
        $info = self::find()->where(['tag_id' => $tag_id])->one();
        if ($info) {
            return $info;
        }
        //创建新的数据
        $model = new TagRecord();
        $model->tag_id = $tag_id;
        $model->like_count = 0;
        $model->link_content_count = 0;
        $model->user_count = 0;
        $model->update_time = 0;
        $model->insert();
        return $model;
    }

    /**
     * 给标签增加一次点赞的数量，触发时间，添加标签/床创建内容依赖条件无
     */
    public static function addLikeCount($tag_id) {
        //没有取消点赞，所以可以直接记数
        $model = self::initData($tag_id);
        $model->like_count += 1;
        $model->update_time = time();
        $model->save();
        return $model->like_count;
    }

    /**
     * 添加标签相关的内容的数量，触发时间，创建内容，触发条件，无
     * @param int $tag_id
     */
    public static function addLikeContentCount($tag_id) {
        $model = self::initData($tag_id);
        $model->link_content_count += 1;
        $model->update_time = time();
        $model->save();
        return $model->link_content_count;
    }

    /**
     * 增加 标签相关的用户的数量，触发时间，添加内容/添加标签，触发条件，当前用户没有和这个标签进行点赞的操作的时候,所以需要记录点赞的
     */
    public static function addUserCount($tag_id, $user_id) {
        //检查当前的用户和这个标签是不是有点赞的记录
        $check = ContentTagList::checkUserTagRelation($tag_id, $user_id);
        if ($check) {
            return TRUE;
        }
        //否则进行数量的+1
        $model = self::initData($tag_id);
        $model->user_count += 1;
        $model->update_time = time();
        $model->save();
        return $model->user_count;
    }

    /**
     * 获取前几个标签
     * @param string $s_type
     * @param int $num
     */
    public static function getHotTagListBySort($s_type, $num = 10) {


        switch ($s_type) {
            case UserStatus::VALUE_RECOMMENT_CONTENT:
                $list = self::find()->limit($num)->orderBy(['link_content_count' => SORT_DESC])->all();

                break;
            case UserStatus::VALUE_RECOMMENT_LIKE:
                $list = self::find()->limit($num)->orderBy(['like_count' => SORT_DESC])->all();

                break;
            case UserStatus::VALUE_RECOMMENT_USER:
                $list = self::find()->limit($num)->orderBy(['user_count' => SORT_DESC])->all();

                break;
            default:
                $list = self::find()->limit($num)->orderBy(['like_count' => SORT_DESC])->all();
                break;
        }
        
        $ids=[];
        if($list){
            foreach ($list as $value) {
                $ids[]=$value->tag_id;
            }
        }

        return $ids;
    }

}
