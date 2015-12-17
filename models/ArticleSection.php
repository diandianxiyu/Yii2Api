<?php

namespace app\models;

use Yii;
use app\models\ArticleItem;

/**
 * This is the model class for table "article_section".
 *
 * @property integer $id
 * @property integer $tid
 * @property string $title
 * @property string $subtitle
 * @property string $cover
 * @property integer $status
 * @property integer $online_time
 * @property string $top
 */
class ArticleSection extends \yii\db\ActiveRecord {

    const STATUS_TOP = 10; //置顶 v1.0
    const STATUS_ONLINE = 9; //上线 v1.1
    const STATUS_TIMEUP = 8; //定时发布  v1.1
    const STATUS_ONLINE_v12 = 7; //上线 v1.2
    const STATUS_TIMEUP_v12 = 6; //定时法布 v1.2
    const STATUS_FOCUS = 5;   //变成焦点图
    const STATUS_OFFLINE = 1; //下线
    const STATUS_DELETE = 4; //删除
    const STATUS_RECOVERY = 3; //放到回收站
    
    
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'article_section';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['tid', 'status', 'online_time'], 'integer'],
            [['title'], 'string', 'max' => 180],
            [['subtitle'], 'string', 'max' => 640],
            [['cover', 'top'], 'string', 'max' => 120]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'tid' => '稿件的 id',
            'title' => '标题',
            'subtitle' => '副标题',
            'cover' => '封面',
            'status' => '状态，未上线1定时发布自动上线，未上线没有定时需要点击上线才可以上线2，上线8，回收站3，删除4',
            'online_time' => '上线时间',
            'top' => '角标显示，json 存入数据，1为 new',
        ];
    }

    /**
     * 获取稿件的列表
     * @param  int $time 时间
     * @param int $count  每页的数量
     * @param int $page 第几页
     * @param int $os 操作系统，根据操作系统返回对应的数据
     */
    public function getPressList($time, $count, $page) {
        $list = ArticleSection::find()
                ->andWhere("status >= :status", [':status' => 8])
                ->andWhere("online_time <= :time", [':time' => $time])
                ->orderBy(['online_time' => SORT_DESC])
                ->select(['id', 'tid', 'title', 'subtitle', 'cover', 'online_time', 'top'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $key => $value) {
                $att = $value->attributes;
                foreach ($att as $key1 => $value1) {
                    if ($value1 == null) {
                        unset($att[$key1]);
                    }
                    $att['cover'] = str_replace(Yii::$app->params['oss_source_url'], Yii::$app->params['oss_for_cdn_url'], $att['cover']);
                }
                $arr[] = $att;
            }
            return $arr;
        } else {
            return [];
        }
    }

    /**
     * 获取稿件的列表
     * @param  int $time 时间
     * @param int $count  每页的数量
     * @param int $page 第几页
     */
    public function getPressListNextPage($time, $count, $page) {
        $page++;
        $list = ArticleSection::find()
                ->andWhere("status >= :status", [':status' => 8])
                ->andWhere("online_time <= :time", [':time' => $time])
                ->orderBy(['online_time' => SORT_DESC])
                ->select(['id'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取稿件的列表
     * @param  int $time 时间
     * @param int $count  每页的数量
     * @param int $page 第几页
     * @param int $os 操作系统，根据操作系统返回对应的数据
     */
    public function getPressListV2($time, $count, $page) {
        $list = ArticleSection::find()
                ->andWhere("status >= :status", [':status' => 6])
                ->andWhere("online_time <= :time", [':time' => $time])
                ->orderBy(['online_time' => SORT_DESC])
                ->select(['id', 'tid', 'title', 'cover', 'online_time', 'top', 'status'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $key => $value) {
                $att = $value->attributes;
                foreach ($att as $key1 => $value1) {
                    if ($value1 == null) {
                        unset($att[$key1]);
                    }

                    if (isset($att['cover'])) {

                        $att['type'] = 1; //稿件
                        $att['share_url']=self::getShareUrl($value['tid']);
                    } else {

                        $att['type'] = 2; //商品
                    }
                }
                $arr[] = $att;
            }
            return $arr;
        } else {
            return [];
        }
    }

    /**
     * 获取稿件的列表
     * @param  int $time 时间
     * @param int $count  每页的数量
     * @param int $page 第几页
     */
    public function getPressListNextPageV2($time, $count, $page) {
        $page++;
        $list = ArticleSection::find()
                ->andWhere("status >= :status", [':status' => 6])
                ->andWhere("online_time <= :time", [':time' => $time])
                ->orderBy(['online_time' => SORT_DESC])
                ->select(['id'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取主键的id
     * @param int $tid 稿件id
     */
    public static function getArticleId($tid) {
        $info = self::find()->where(['tid' => $tid])->select(['id'])->one();
        return $info['id'];
    }

    /**
     * 获取稿件的 tid
     * @param int $tid 稿件id
     */
    public static function getTId($tid) {
        $info = self::find()->where(['id' => $tid])->select(['tid'])->one();
        return $info['tid'];
    }

    /**
     * 判断稿件是不是存在
     * @param int $tid 稿件id
     */
    public static function checkExist($tid) {
        $info = self::find()->select(['id'])->where(['tid' => $tid])->andWhere("status >= :status", [':status' => 8])->one();
        if ($info) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 判断稿件是不是存在
     * @param int $tid 稿件id
     */
    public static function checkExistV2($tid) {
        //设置5是因为5是置顶的，也是上线标准
        $info = self::find()->where(['tid' => $tid])->andWhere("status >= :status", [':status' => 5])->one();
        if ($info) {
            return $info->attributes;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取相关的推荐的稿件
     * @param int $id 稿件的id
     */
    public static function getRecommend($id) {
        //获取上线时间
        $info = self::find()->select(['online_time'])->where(['id' => $id])->one();

        $list = ArticleSection::find()
                ->andWhere("status >= :status", [':status' => 6])
                ->andWhere("top != :top", [':top' => "1"])
                ->andWhere("online_time < :time", [':time' => $info['online_time']])
                ->orderBy(['online_time' => SORT_DESC])
                ->limit(2)
                ->all();

        if (count($list)) {
            $return = [];
            foreach ($list as $value) {
                $arr = $value->attributes;
                //获取缩略图
                if ($arr['status'] == 8 || $arr['status'] == 9) {
                    //获取第一个商品的图片
                    $thumb = ArticleItem::getFirstThumb($value['id']);
                    $arr['cover'] = str_replace(Yii::$app->params['oss_source_url'], Yii::$app->params['oss_for_cdn_url'], $thumb);
                } else {
                    $arr['cover'] = str_replace(Yii::$app->params['oss_source_url'], Yii::$app->params['oss_for_cdn_url'], $value['cover']);
                }
                $arr['share_url']=self::getShareUrl($value['tid']);
                
                unset($arr['id']);
                unset($arr['subtitle']);
                unset($arr['status']);
                unset($arr['online_time']);
                unset($arr['top']);
                $return[] = $arr;
            }
            return $return;
        } else {
            return [];
        }
    }
    
        
    /**
     * 获取
     */
    public static function getTopList(){
        $query = self::find()
                ->andWhere("status = :top  ", [':top' => self::STATUS_FOCUS])
                ->orderBy(['online_time' => SORT_DESC])
                ->all();
        return $query;
    }
    
    
    /**
     * 获取稿件的列表
     * @param string $word 要搜索的词所在的稿件id
     * @param  int $time 时间
     * @param int $count  每页的数量
     * @param int $page 第几页
     * @param int $os 操作系统，根据操作系统返回对应的数据
     */
    public static function getPressListV2BySerach($word,$word_ids,$time, $count, $page) {
       // return $word;
        
        //在把对应的article_id放在对应的稿件里面进行查询
        $list = ArticleSection::find()
                ->where(['in','id',$word_ids])
                ->orWhere(['like','title',$word])
                ->andWhere("status >= :status", [':status' => 5]) //可以搜到置顶的
                ->andWhere("online_time <= :time", [':time' => $time])
                ->orderBy(['online_time' => SORT_DESC])
                ->select(['id', 'tid', 'title', 'cover', 'online_time', 'top', 'status'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $key => $value) {
                $att = $value->attributes;
                foreach ($att as $key1 => $value1) {
                    if ($value1 == null) {
                        unset($att[$key1]);
                    }
                    if (isset($att['cover'])) {
                        //1是稿件
                        $att['type'] = 1;
                        $att['share_url']=self::getShareUrl($value['tid']);
                    } else {

                        //2是商品
                        $att['type'] = 2;
                    }
                }
                $arr[] = $att;
            }
            return $arr;
        } else {
            return [];
        }
    }

    /**
     * 获取稿件的列表
     * @param string $word 要搜索的词所在的稿件id
     * @param  int $time 时间
     * @param int $count  每页的数量
     * @param int $page 第几页
     */
    public function getPressListNextPageV2BySerach($word,$word_ids,$time, $count, $page) {
        $page++;
        $list = ArticleSection::find()
                ->where(['in','id',$word_ids])->orWhere(['like','title',$word])
                ->andWhere("status >= :status", [':status' => 5])
                ->andWhere("online_time <= :time", [':time' => $time])
                ->orderBy(['online_time' => SORT_DESC])
                ->select(['id'])
                ->offset($count * $page)
                ->limit($count)
                ->all();
        if ($list) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    
    /**
     * 获取文字相关的稿件的id和名称
     * @param type $word 要搜索的词
     * @return int
     */
    public static function getPressListV2BySerachAll($word) {
        $time=  time();
        //在把对应的article_id放在对应的稿件里面进行查询
        $list = ArticleSection::find()
                ->orWhere(['like','title',$word])
                ->andWhere("status >= :status", [':status' => 5]) //可以搜到置顶的
                ->andWhere(" top != :top ",[':top'=>"1"])
                ->andWhere("online_time <= :time", [':time' => $time])
                ->orderBy(['online_time' => SORT_DESC])
                ->select(['id', 'tid', 'title', 'cover', 'online_time', 'top', 'status'])
                ->all();
        if ($list) {
            $arr = [];
            foreach ($list as $key => $value) {
                $att = $value->attributes;
                foreach ($att as $key1 => $value1) {
                    if ($value1 == null) {
                        unset($att[$key1]);
                    }
                    if (isset($att['cover'])) {
                        //1是稿件
                        $att['type'] = 1;
                        $att['share_url']=self::getShareUrl($value['tid']);
                    } else {
                        //2是商品
                        $att['type'] = 2;
                    }
                }
                $attt=(string)$att['online_time'];
                $arr[$attt] = $att;
            }
            return $arr;
        } else {
            return [];
        }
    }
    
    /**
     * 得到稿件的分享地址
     * @param int $tid 稿件id
     */
    public static function getShareUrl($tid){
        return Yii::$app->params['url_host'] . 'press?tid=' . $tid ;
    }


}
