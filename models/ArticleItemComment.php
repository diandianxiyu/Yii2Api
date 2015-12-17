<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_item_comment".
 *
 * @property integer $id
 * @property integer $item_id
 * @property integer $user_id
 * @property integer $create_time
 * @property integer $disabled
 * @property string $text
 */
class ArticleItemComment extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'article_item_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['item_id', 'user_id', 'create_time', 'disabled'], 'integer'],
            [['text'], 'string', 'max' => 960]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'item_id' => '稿件的 id',
            'user_id' => '用户的 id',
            'create_time' => '创建的时间',
            'disabled' => '是否被删除',
            'text' => '展示的文字',
        ];
    }

    /**
     * 创建一个评论
     * @param int $item_id  稿件的主键id
     * @param int $user_id   用户的主键id
     * @param string $text   弹幕的内容
     */
    public static function addComment($item_id, $user_id, $text) {
        $model = new ArticleItemComment();
        $model->item_id = $item_id;
        $model->user_id = $user_id;
        $model->disabled = 0;
        $model->create_time = time();
        $model->text = $text;
        if ($model->save()) {
            return $model->id;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取评论信息
     * @param  int $id 主键
     */
    public static function getOneComment($id) {
        $info = self::find()->where(['id' => $id])->andWhere(['disabled' => 0])->select(['id','item_id', 'user_id', 'create_time', 'text'])->one();
        if ($info) {
            //格式化输出
            $comment['id'] = $info['id'];
            $comment['uid'] = UserAccount::getUid($info['user_id']);
            $comment['create_time'] = $info['create_time'];
            $comment['text'] = $info['text'];
            return $comment;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取弹幕列表
     * @param int $item_id 稿件id
     * @param int $time 时间的限制
     * @param int $count 获取的数量
     * @param int $page 页码
     */
    public static function getList($item_id, $time, $count, $page) {
        $list = self::find()->where(['item_id' => $item_id])
                        ->andWhere(["disabled" => 0])
                        ->select(['id','user_id', 'create_time', 'text'])
//                        ->andWhere(" create_time <= :time", [':time' => $time])
                        ->limit($count)
                        ->offset($count * $page)
                        ->orderBy(['create_time' => SORT_ASC])->all();
        if ($list) {
            //组装
            $arr = [];
            foreach ($list as $value) {
                $att = $value->attributes;
                $one = [];
                $one['id'] = $att['id'];
                $one['uid'] = UserAccount::getUid($att['user_id']);
                $one['create_time'] = $att['create_time'];
                $one['text'] = $att['text'];
                $arr[] = $one;
            }
            return $arr;
        } else {
            return [];
        }
    }

    /**
     * 获取弹幕列表
     * @param int $item_id 稿件id
     * @param int $time 时间的限制
     * @param int $count 获取的数量
     * @param int $page 页码
     */
    public static function getNextPage($item_id, $time, $count, $page) {
        $page++;
        $list = self::find()->where(['item_id' => $item_id])
                        ->andWhere(["disabled" => 0])
                        ->select(['id'])
//                        ->andWhere(" create_time <= :time", [':time' => $time])
                        ->limit($count)
                        ->offset($count * $page)
                        ->orderBy(['create_time' => SORT_DESC])->all();
        if ($list) {
            //组装
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    
    /**
     * 获取对应的评论的作者
     * @param int $id 主键
     */
    public static function getUserIdById($id){
        $info=  self::find()->where(['id'=>$id])->one();
        if($info){
            $att=$info->attributes;
            return $att['user_id'];
        }else{
            return FALSE;
        }
    }
    
    /**
     * 删除
     * @param type $id
     */
    public static function del($id){
        return self::updateAll([
            'disabled'=>1,
        ], ['id'=>$id]);
    }

}
