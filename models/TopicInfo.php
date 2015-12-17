<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "topic_info".
 *
 * @property integer $id
 * @property string $name
 * @property string $pic
 * @property string $desc
 * @property integer $update_time
 * @property integer $sort
 * @property integer $disabled
 * @property integer $status
 */
class TopicInfo extends \yii\db\ActiveRecord {
    /*
     * 上线的状态
     */

    const STATUS_DISABLED_ONLINE = 0;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'topic_info';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['update_time', 'sort', 'disabled', 'status'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['pic'], 'string', 'max' => 600],
            [['desc'], 'string', 'max' => 960]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'name' => '主题名称',
            'pic' => '对应图片',
            'desc' => '专题描述',
            'update_time' => '创建的时间',
            'sort' => '排序字段',
            'disabled' => '是否被禁用，默认1，上线状态就是0',
            'status' => '保留字段，默认1',
        ];
    }

    /**
     * 分页获取对应的主题列表
     * @param type $count
     * @param type $page
     */
    public static function getList($count, $page) {
        $list = self::find()
                ->andWhere(['disabled' => self::STATUS_DISABLED_ONLINE])
                ->orderBy(['sort' => SORT_DESC])
                ->select(['id', 'name', 'pic'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $value) {
                $att = $value->attributes;
                foreach ($att as $key1 => $value1) {
                    if ($value1 == null) {
                        unset($att[$key1]);
                    }
                }
                $att['topic_id'] = $att['id'];
                unset($att['id']);
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
    public static function checkNextPage($count, $page) {
        $page++;
        $list = self::find()
                ->andWhere(['disabled' => self::STATUS_DISABLED_ONLINE])
                ->orderBy(['sort' => SORT_DESC])
                ->select(['id'])
                ->offset($count * $page)
                ->limit($count)
                ->all();

        if ($list) {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * 获取话题的信息
     * @param int $id 主键
     */
    public static function getInfo($id){
        $att=  self::find()->where(['id'=>$id])->one();
        if($att){
             return $att->attributes;
        }
        return FALSE;
       
    }
    
    /**
     * 获取话题的名字
     * @param int $id 话题的id
     */
    public static function getNameById($id){
        $att=  self::find()->where(['id'=>$id])->andWhere(['disabled' => self::STATUS_DISABLED_ONLINE])->one();
        if($att){
             return $att->name;
        }
        return "";
    }

}
