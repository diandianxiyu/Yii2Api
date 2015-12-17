<?php

namespace app\models;

use Yii;
use app\models\TopicInfo;
use app\models\TagRecord;
use app\models\ContentTagList;

/**
 * This is the model class for table "content_base".
 *
 * @property integer $id
 * @property integer $content_cid
 * @property integer $user_id
 * @property string $text
 * @property integer $topic_id
 * @property string $pic
 * @property integer $update_time
 * @property integer $status
 * @property integer $create_time
 */
class ContentBase extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'content_base';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['content_cid', 'user_id', 'topic_id', 'update_time', 'status', 'create_time'], 'integer'],
            [['text'], 'string', 'max' => 960],
            [['pic'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'content_cid' => '对外显示用的id',
            'user_id' => '用户表的主键',
            'text' => '文字说明',
            'topic_id' => '话题的主键',
            'pic' => '图片地址',
            'update_time' => '最后被加入标签和修改的时间',
            'status' => '状态，保留字段，目前是1', //2，表示被删除，3 表示被后台删除
            'create_time' => '创建时间',
        ];
    }

    /**
     * 创建一条内容
     * @param int $content_cid 
     * @param int $user_id
     * @param string $text
     * @param string $pic
     */
    public static function add($content_cid, $user_id, $text, $pic, $topic_id = 0) {
        $model = new ContentBase();
        $model->content_cid = $content_cid;
        $model->user_id = $user_id;
        $model->text = $text;
        $model->pic = $pic;
        $model->update_time = time();
        $model->status = 1;
        $model->create_time = time();
        $model->topic_id = $topic_id;
        $model->insert();
        return $model->id;
    }

    /**
     * 刷新用户的内容的最后的活跃的时间
     * @param int $id 主键
     */
    public static function refreshUpdateTime($id) {
        return self::updateAll([
                    'update_time' => time()
                        ], ['id' => $id]);
    }

    /**
     * 根据Cid获取主键的id
     * @param int $cid 用于标识的主键
     */
    public static function getIdByCid($cid) {
        $info = self::find()->where(['content_cid' => $cid])->one();
        if ($info) {
            //返回主键
            return $info->id;
        }
        return FALSE;
    }

    /**
     * 获取内容的信息
     * @param int $content_cid 稿件的id
     */
    public static function getInfoByCid($content_cid) {
        $infos = self::find()->where(['content_cid' => $content_cid])->one();
        //返回的信息
        if ($infos) {
            $att = $infos->attributes;
            $info = [];
            $info['content_cid'] = $att['content_cid'];
            $info['user_id'] = $att['user_id'];
            $info['text'] = $att['text'];
            $info['pic'] = $att['pic'];
            $info['topic_id'] = $att['topic_id'];
            $info['topic_name'] = TopicInfo::getNameById($att['topic_id']);
            $info['create_time'] = $att['create_time'];
            $info['share_url'] = self::getShareUrl($att['content_cid']);
            return $info;
        }

        return FALSE;
    }

    /**
     * 获取分享地址
     * @param type $content_cid
     */
    private static function getShareUrl($content_cid) {
        return Yii::$app->params['url_host'] . "content?cid=" . $content_cid;
    }

    /**
     * 获取内容的信息
     * @param int $content_id 稿件的id
     */
    public static function getInfoById($content_id) {
        $infos = self::find()->where(['id' => $content_id])->one();
        //返回的信息
        if ($infos) {
            $att = $infos->attributes;
            $info = [];
            $info['content_cid'] = $att['content_cid'];
            $info['user_id'] = $att['user_id'];
            $info['text'] = $att['text'];
            $info['pic'] = $att['pic'];
            $info['topic_id'] = $att['topic_id'];
            $info['topic_name'] = TopicInfo::getNameById($att['topic_id']);
            $info['create_time'] = $att['create_time'];
            return $info;
        }

        return FALSE;
    }

    /**
     * 分页获取对应的主题列表
     * @param type $count
     * @param type $page
     */
    public static function getList($user_id, $count, $page) {
        $list = self::find()
                ->andWhere(['status' => 1])
                ->orderBy(['create_time' => SORT_DESC])
                ->select(['content_cid'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $value) {
                $att = $value->attributes;

                $arr[] = $att['content_cid'];
            }
            return $arr;
        } else {
            return [];
        }
    }

    /**
     * 返回对应的下一页是不是存在
     * @param type $count
     * @param type $page
     */
    public static function checkNextPage($user_id, $count, $page) {
        $page++;
        $list = self::find()
                ->andWhere(['status' => 1])
                ->orderBy(['create_time' => SORT_DESC])
                ->select(['content_cid'])
                ->offset($count * $page)
                ->limit($count)
                ->all();

        if ($list) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 分页获取对应的主题列表
     * @param type $count
     * @param type $page
     */
    public static function getListByUserId($user_id, $count, $page) {
        $list = self::find()
                ->andWhere(['status' => 1])
                ->andWhere(['user_id' => $user_id])
                ->orderBy(['create_time' => SORT_DESC])
                ->select(['content_cid'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $value) {
                $att = $value->attributes;

                $arr[] = $att['content_cid'];
            }
            return $arr;
        } else {
            return [];
        }
    }

    /**
     * 返回对应的下一页是不是存在
     * @param type $count
     * @param type $page
     */
    public static function checkNextPageByUserId($user_id, $count, $page) {
        $page++;
        $list = self::find()
                ->andWhere(['status' => 1])
                ->andWhere(['user_id' => $user_id])
                ->orderBy(['create_time' => SORT_DESC])
                ->select(['content_cid'])
                ->offset($count * $page)
                ->limit($count)
                ->all();

        if ($list) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 根据话题获取列表
     */
    public static function getListByTopicId($topic_id, $count, $page) {
        $list = self::find()
                ->andWhere(['status' => 1])
                ->andWhere(['topic_id' => $topic_id])
//                ->andWhere("  update_time  >= :t  ",[':t'=>  time() - (18*3600)])
                ->orderBy(['create_time' => SORT_DESC])
                ->select(['content_cid'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $value) {
                $att = $value->attributes;

                $arr[] = $att['content_cid'];
            }
            return $arr;
        } else {
            return [];
        }
    }

    /**
     * 返回对应的下一页是不是存在
     */
    public static function checkNextPageByTopicId($topic_id, $count, $page) {
        $page++;
        $list = self::find()
                ->andWhere(['status' => 1])
                ->andWhere(['topic_id' => $topic_id])
//                ->andWhere("  update_time  >= :t  ",[':t'=>  time() - (18*3600)])
                ->orderBy(['create_time' => SORT_DESC])
                ->select(['content_cid'])
                ->offset($count * $page)
                ->limit($count)
                ->all();

        if ($list) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 标记删除
     * @param type $content_id
     */
    public static function del($content_id) {
        self::updateAll(['status' => 0], ['id' => $content_id]);
    }
    
    /**
     * 更新最后的操作的时间
     * @param int  $content_id 主键
     */
    public static function updateUpdateTime($content_id){
        self::updateAll(['update_time' => time()], ['id' => $content_id]);
    }
    
    /**
     * 获取对应的用户内容
     */
    public static function getRecommentContentPage($tags,$c_type,$page=0,$count=20,$time=0){
       //第二步，通过连表的方式，把标签表和用户的表联系在一起
       $content_list= ContentTagList::getRecommentList($c_type,$tags,$page,$count,$time);
       return $content_list; 
    }
    
    /**
     * 看看有没有下一页
     */
    public static function getRecommentContentPageNextPage($tags,$c_type,$page=0,$count=20,$time=0){
       //第二步，通过连表的方式，把标签表和用户的表联系在一起
       $content_list= ContentTagList::getRecommentListNextPage($c_type,$tags,$page,$count,$time);
       return $content_list; 
    }
}
