<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_detail".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $item_id
 * @property string $open_iid
 * @property integer $create_time
 */
class UserDetail extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'user_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'item_id', 'create_time'], 'integer'],
            [['open_iid'], 'string', 'max' => 24]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'user_id' => '用户的 id',
            'item_id' => '商品的 id',
            'open_iid' => '商品的唯一标识',
            'create_time' => '创建的时间',
        ];
    }

    /**
     * 检查是不是已经加入了心愿清单
     * @param  int $user_id 用户的主键 id
     * @param int $item_id 商品的主键 id
     */
    public function checkDetail($user_id, $open_iid, $item_id) {
        $info = UserDetail::find()->where(['user_id' => $user_id])->andWhere(['item_id' => $item_id])->andWhere(['open_iid' => $open_iid])->one();
        if ($info) {
            return true;
        } else {
            return FALSE;
        }
    }

    /**
     * 检查这条心愿清单是不是自己的
     * @param  int $id 用户的主键 id
     * @param int $user_id 商品的主键 id
     */
    public function checkDetailById($id, $user_id) {
        $info = UserDetail::find()->where(['id' => $id])->andWhere(['user_id' => $user_id])->one();
        if ($info) {
            return $info->attributes;
        } else {
            return FALSE;
        }
    }
    
    /**
     * 加入心愿清单
     * @param  int $user_id 用户表主键
     * @param  int $open_iid 商品唯一标识
     * @param  int $item_id 商品表主键
     */
    public static function add($user_id, $open_iid, $item_id) {
        self::deleteAll(['user_id' => $user_id, 'item_id' => $item_id]);
        $model = new UserDetail();
        $model->user_id = $user_id;
        $model->item_id = $item_id;
        $model->open_iid = $open_iid;
        $model->create_time = time();
        $model->insert();
        return $model->id;
    }

    /**
     * 删除商品,保留数据
     * @param  int $user_id 用户的主键
     * @param  int $item_id 商品的主键
     */
    public static function del($user_id, $item_id) {
        return self::deleteAll(['user_id' => $user_id, 'item_id' => $item_id]);
    }

    /**
     * 返回当前的被收藏的数量
     * @param  int $item_id 稿件的主键 id
     */
    public static function getCountsByItemId($item_id) {
        return (int) self::find()->where(['item_id' => $item_id])->andWhere(" create_time != :time ", [':time' => 0])->count();
    }

    /**
     * 获取收藏的稿件
     * @param  int $time 时间
     * @param int $count  每页的数量
     * @param int $page 第几页
     */
    public function getList($user_id,$time, $count, $page) {
        $list = UserDetail::find()
                ->andWhere("create_time <= :time", [':time' => $time])
                ->andWhere(['user_id'=>$user_id])
                ->orderBy(['create_time' => SORT_DESC])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $key => $value) {
                $att = $value->attributes;
                unset($att['user_id']);
                $arr[] = $att;
            }
            return $arr;
        } else {
            return [];
        }
    }

    /**
     * 获取稿件的列表
     * @param int $user_id
     * @param  int $time 时间
     * @param int $count  每页的数量
     * @param int $page 第几页
     */
    public function getListNextPage($user_id,$time, $count, $page) {
        $page++;
        $list = UserDetail::find()
                ->where("create_time <= :time", [':time' => $time])
                ->orderBy(['create_time' => SORT_DESC])
                ->andWhere(['user_id'=>$user_id])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if (count($list)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}
