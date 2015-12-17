<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "article_item".
 *
 * @property integer $id
 * @property integer $article_id
 * @property integer $iid
 * @property string $title
 * @property string $author
 * @property integer $status
 * @property integer $sort
 * @property string $thumb
 * @property string $thumb_page
 * @property string $thumb_list
 * @property string $promotion_price
 * @property string $price
 * @property string $open_iid
 * @property string $profile
 * @property integer $online_time
 * @property string $contents
 */
class ArticleItem extends \yii\db\ActiveRecord {

    const STATUS_ONLINE=1;   //上线
    const STATUS_OFFLINE=2;  //下线
    const STATUS_DEL=3;     //删除
    const STATUS_KILL=4;    //彻底删除
    //判断稿件的相对的状态
    const STATUS_YORI_ONLINE=6; //不属于任何稿件的游离状态
    const STATUS_YORI_OFFLINE=5;  //下线的状态
    const STATUS_YORI_DEL=7; //不属于任何稿件的游离状态的被删除的状态
    const STATUS_YORI_KILL=8;  //被彻底删除的状态

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'article_item';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['article_id', 'iid', 'status', 'sort', 'online_time'], 'integer'],
            [['contents'], 'string'],
            [['title', 'author', 'thumb', 'thumb_page', 'thumb_list'], 'string', 'max' => 180],
            [['promotion_price', 'price'], 'string', 'max' => 32],
            [['open_iid'], 'string', 'max' => 96],
            [['profile'], 'string', 'max' => 720]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'article_id' => '所属的稿件 id',
            'iid' => '商品的 展示类id',
            'title' => '标题',
            'author' => '作者',
            'status' => '状态， 1 上线 2 下线 3 删除',
            'sort' => '排序',
            'thumb' => '稿件页面缩略图',
            'thumb_page' => '商品详情页面的商品缩略图',
            'thumb_list' => '心愿清单的稿件缩略图',
            'promotion_price' => '促销价格',
            'price' => '原价',
            'open_iid' => '商品的唯一 openiid',
            'profile' => '稿件页面的简介',
            'online_time' => '上线时间',
            'contents' => '正文',
        ];
    }

    /**
     * 获取商品数量
     * @param int $article_id 稿件的 id
     */
    public static function getCountByTid($article_id) {
        return (int) self::find()->where(['article_id' => $article_id, 'status' => self::STATUS_ONLINE])->count();
    }

    /**
     * 检查是不是存在
     * @param type $iid
     * @return boolean
     */
    public static function checkExist($iid) {
        $info = self::find()->where(['iid' => $iid])->one();
        if ($info) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取全部的稿件列表
     * @param type $article_id
     */
    public static function getList($article_id, $os, $uid,$v=1) {
        $list = ArticleItem::find()->where(['article_id' => $article_id])->select(['id', 'iid', 'title', 'open_iid', 'author', 'thumb', 'promotion_price', 'price', 'online_time', 'profile'])
                        ->andWhere(['status' => 1])
                        ->orderBy(['sort' => SORT_ASC, 'online_time' => SORT_DESC])->all();
        if ($list) {
            $arr = [];
            foreach ($list as $key => $value) {
                $att = $value->attributes;
                foreach ($att as $key1 => $value1) {
                    if (!in_array($key1, ['open_iid', 'promotion_price', 'price'])) {
                        if ($value1 == null) {
                            unset($att[$key1]);
                        }
                    } else {
                        if ($value1 == null) {
                            $att[$key1] = "";
                        }
                    }
                    $att['discount_price'] = $att['promotion_price'];

                    $att['thumb'] = str_replace(Yii::$app->params['oss_source_url'], Yii::$app->params['oss_for_cdn_url'], $att['thumb']);
                    //稿件页面和对外分享页面
                    $att['url'] = self::getUrl($att['iid'], $os, $uid,$v);
                    $att['share_url'] = self::getShareUrl($att['iid'], $uid,$v);
                }
                $arr[] = $att;
            }
            return $arr;
        } else {
            return [];
        }
    }
    
    /**
     * 获取单个商品信息
     * @param int $iid
     * @param int $os
     * @param int $uid
     * @param int $v
     */
    public static function getOne($iid, $os, $uid,$v=1){
        $list = ArticleItem::find()->where(['id' => $iid])->select(['id', 'iid', 'title', 'open_iid', 'author', 'thumb', 'promotion_price', 'price', 'online_time', 'profile'])
                        
                        ->orderBy(['sort' => SORT_ASC, 'online_time' => SORT_DESC])->all();
//        return $list;
        if ($list) {
            $arr = [];
            foreach ($list as $key => $value) {
                $att = $value->attributes;
                foreach ($att as $key1 => $value1) {
                    if (!in_array($key1, ['open_iid', 'promotion_price', 'price'])) {
                        if ($value1 == null) {
                            unset($att[$key1]);
                        }
                    } else {
                        if ($value1 == null) {
                            $att[$key1] = "";
                        }
                    }
                    $att['discount_price'] = $att['promotion_price'];

                    $att['thumb'] = str_replace(Yii::$app->params['oss_source_url'], Yii::$app->params['oss_for_cdn_url'], $att['thumb']);
                    //稿件页面和对外分享页面
                    $att['url'] = self::getUrl($att['iid'], $os, $uid,$v);
                    $att['share_url'] = self::getShareUrl($att['iid'], $uid,$v);
                }
                $arr[] = $att;
            }
            return $arr[0];
        } else {
            return [];
        }
    }

    /**
     * 获取详细地址
     * @param  int $tid  
     */
    public static function getUrl($iid, $os = 1, $uid = 0,$v=1) {
        $http = Yii::$app->params['url_host'] . "item-page";
        if($v==2){//V1.2
            $http = Yii::$app->params['url_host'] . "items-view";
        }
        return $http . '?tid=' . $iid . '&os=' . $os . '&u=' . $uid;
    }

    /**
     * 获取分享的网址
     * @param  int $tid  稿件 id
     */
    public static function getShareUrl($iid, $uid = 0,$v=1) {
        if($v==2){//V1.2
            return Yii::$app->params['url_host'] . 'items?tid=' . $iid . '&u=' . $uid;
        }
        return Yii::$app->params['url_host'] . 'item?tid=' . $iid . '&u=' . $uid;
    }

    /**
     * 获取商品id通过 iid
     * @param int $iid 稿件的 id
     */
    public static function getIdByIid($iid) {
        $info = self::find()->where(['iid' => $iid, 'status' => self::STATUS_ONLINE])->one();
        if ($info) {
            $att = $info->attributes;
            return $att['id'];
        } else {
            return FALSE;
        }
    }

    /**
     * 获取商品id通过 iid
     * @param int $id 稿件的 id
     */
    public static function getIidById($id) {
        $info = self::find()->where(['id' => $id, 'status' => self::STATUS_ONLINE])->one();
        if ($info) {
            $att = $info->attributes;
            return $att;
        } else {
            return FALSE;
        }
    }

    /**
     * 根据 iid 获取对应的数据
     * @param  int $iid 商品的信息
     */
    public static function getInfoByIid($iid) {
        $info = self::find()->where(['iid' => $iid])->one();
        if ($info) {
            return $info->attributes;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取第一个的图
     * @param type $article_id
     */
    public static function getFirstThumb($article_id) {
        $list = ArticleItem::find()->limit(1)
                        ->where(['article_id' => $article_id])->select(['thumb'])
                        ->andWhere(['status' => 1])
                        ->orderBy(['sort' => SORT_ASC, 'online_time' => SORT_DESC])->all();
        return $list[0]['thumb'];
    }
    
    /**
     * 获取单词相关的稿件id，包含稿件相关的名字
     * @return array 稿件id
     */
    public static function getSerachByWords($word,$os,$uid){ 
        
         $ids= self::find()->select(['id', 'iid', 'title', 'open_iid', 'author', 'thumb', 'promotion_price', 'price', 'online_time', 'profile'])
                 ->where(['like','title',$word])
                 ->orWhere(['like','profile',$word])
                 ->andWhere(['in','status',[self::STATUS_ONLINE,  self::STATUS_YORI_ONLINE]])
//                 ->groupBy(['article_id'])
                 ->orderBy(['online_time'=>SORT_DESC])
                 ->all();
        
        //整理出稿件的id
        $ids_arr=[];
        if($ids){  
            foreach ($ids as $value) {
                $att = $value->attributes;
                foreach ($att as $key1 => $value1) {
                    if (!in_array($key1, ['open_iid', 'promotion_price', 'price'])) {
                        if ($value1 == null) {
                            unset($att[$key1]);
                        }
                    } else {
                        if ($value1 == null) {
                            $att[$key1] = "";
                        }
                    }
                    $att['discount_price'] = $att['promotion_price'];

                    $att['thumb'] = str_replace(Yii::$app->params['oss_source_url'], Yii::$app->params['oss_for_cdn_url'], $att['thumb']);
                    //稿件页面和对外分享页面
                    $att['url'] = self::getUrl($att['iid'], $os, $uid,2);
                    $att['share_url'] = self::getShareUrl($att['iid'], $uid,2);
                }
                $att['type']=3;
                $key_name=(string)$value->online_time;
                $ids_arr[$key_name]=$att;
            }
        }
        return $ids_arr;
    }

}
