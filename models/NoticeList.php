<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notice_list".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $type
 * @property integer $send_user_id
 * @property integer $tag_id
 * @property integer $status
 * @property integer $send_time
 */
class NoticeList extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notice_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'send_user_id', 'content_id', 'tag_id', 'status', 'send_time'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'user_id' => '用户',
            'type' => '类型，1 添加标签(新的) ， 2 赞同已经存在的标签',
            'send_user_id' => '动作的id',
            'content_id' => '内容的id',
            'tag_id' => '相关的标签id',
            'status' => '默认1，保留字段',
            'send_time' => '发送时间',
        ];
    }

    /**
     * 添加一条记录
     * @param int $user_id 接收人
     * @param int $type 类型
     * @param int $send_user_id 发送人
     * @param int $tag_id 标签id
     */
    public static function add($user_id, $type, $send_user_id, $tag_id,$content_id=0) {
        $model = new NoticeList();

        $model->user_id = $user_id;
        $model->type = $type;
        $model->send_user_id = $send_user_id;
        $model->tag_id = $tag_id;
        $model->status = 1;
        $model->content_id=$content_id;
        $model->send_time = time();

        $model->insert();

        return $model->id;
    }

    /**
     * 分页获取对应的主题列表
     * @param type $count
     * @param type $page
     */
    public static function getList($user_id,$timelimit, $count, $page) {
        $list = self::find()
                ->andWhere(['user_id'=>$user_id])
                ->andWhere(['status' => 1])
                ->orderBy(['send_time' => SORT_DESC])
                ->andWhere(" send_time <= :time", [':time' => $timelimit])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $value) {
                $att = $value->attributes;
                $arr[] = $att;
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
    public static function checkNextPage($user_id,$timelimit,$count, $page) {
        $page++;
        $list = self::find()->andWhere(['user_id'=>$user_id])
                 ->andWhere(['status' => 1])
                ->orderBy(['send_time' => SORT_DESC])
                ->andWhere(" send_time <= :time", [':time' => $timelimit])
                ->select(['id'])
                ->offset($count * $page)
                ->limit($count)
                ->all();

        if ($list) {
            return TRUE;
        }
        return FALSE;
    }

}
