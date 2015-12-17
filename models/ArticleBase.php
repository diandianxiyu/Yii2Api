<?php

namespace app\models;

use Yii;
use app\components\helper\Validators;

/**
 * This is the model class for table "article_base".
 *
 * @property integer $id
 * @property integer $tid
 * @property string $title
 * @property string $summary
 * @property string $author
 * @property integer $online_time
 * @property string $contents
 * @property string $cover_pic
 * @property string $related_pic
 * @property integer $status
 */
class ArticleBase extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'article_base';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'tid', 'title', 'summary', 'author', 'online_time', 'contents', 'cover_pic', 'related_pic'], 'required'],
            [['id', 'tid', 'online_time', 'status'], 'integer'],
            [['contents'], 'string'],
            [['title', 'author', 'cover_pic', 'related_pic'], 'string', 'max' => 120],
            [['summary'], 'string', 'max' => 300]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'tid' => '稿件tid',
            'title' => '标题',
            'summary' => '摘要',
            'author' => '作者',
            'online_time' => '上线时间',
            'contents' => '正文内容',
            'cover_pic' => '封面图片',
            'related_pic' => '推荐图片',
            'status' => '状态，未上线1定时发布自动上线，未上线没有定时需要点击上线才可以上线2，上线9，置顶10，回收站3，删除4',
        ];
    }

    /**
     * 获取稿件的列表
     * @param int $time 限制的时间
     * @param int $count 数量
     * @param int $page  页码，最小的是0
     */
    public static function getArticleList($time, $count, $page, $os,$uid) {
        $list = self::find()->andWhere("status != :topstatus", [':topstatus' => 10])->andWhere("status >= :status", [':status' => 8])->andWhere("online_time <= :time", [':time' => $time])->orderBy(['online_time' => SORT_DESC])
                ->select(['tid', 'title', 'summary', 'online_time', 'cover_pic'])
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
                    //加入链接
                    $att['url'] = self::getUrl($att['tid'], $os,$uid);
                    $att['share_url'] = self::getShareUrl($att['tid'],$uid);
                }
                $arr[] = $att;
            }
            return $arr;
        } else {
            return [];
        }
    }

    /**
     * 是不是有下一页
     * @param int $time 限制的时间
     * @param int $count 数量
     * @param int $page  页码，最小的是0
     */
    public static function articleListNextPage($time, $count, $page) {
        $page++;
        $list = self::find()->andWhere("status != :topstatus", [':topstatus' => 10])->andWhere("status >= :status", [':status' => 8])->andWhere("online_time <= :time", [':time' => $time])->orderBy(['online_time' => SORT_DESC])
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
     * 获取全部的置顶稿件
     */
    public static function getTop($os,$uid) {
        $list = self::find()->where(['status' => 10])->orderBy(['online_time' => SORT_DESC])
                        ->select(['tid', 'title', 'summary', 'online_time', 'cover_pic'])
                        ->one();
        if ($list) {
            $list=$list->attributes;
            foreach ($list as $key => $value) {
                if ($value == NULL) {
                    unset($list[$key]);
                }
            }
            $list['cover_pic'] = Validators::replaceWords($list['cover_pic']);
            $list['url'] = self::getUrl($list['tid'], $os,$uid);
            $list['share_url'] = self::getShareUrl($list['tid'],$uid);
            return $list;
        } else {
            return [];
        }
    }

    /**
     * 获取详细信息
     * @param int $tid  稿件的id
     */
    public static function getInfo($tid, $os,$uid) {
        $info = self::find()->select(['tid', 'title', 'author', 'online_time'])->where(['tid' => $tid])->one()->attributes;
        if ($info) {
            $article['tid'] = $info['tid'];
            $article['title'] = $info['title'];
            $article['author'] = $info['author'];
            $article['online_time'] = $info['online_time'];
            $article['url'] = self::getUrl($info['tid'], $os,$uid);
            $article['share_url'] = self::getShareUrl($info['tid'],$uid);
            return $article;
        } else {
            return FALSE;
        }
    }

    /**
     * 获取这个稿件的下一篇的基本信息
     */
//    public static function getRelated($tid, $os) {
//        //获取时间
//        $info_by_tid = self::find()->where(["tid" => $tid])->select(['online_time'])->one();
//        //状态大于等于9 时间比这个时间小
//        $info = self::find()->select(['tid', 'title', 'related_pic'])->where(" status >= :online  ", [':online' => 9])->andWhere(" online_time < :time ", ['time' => $info_by_tid['online_time']])->one();
//        if ($info) {
//            $att = $info->attributes;
//            $related['tid'] = $att['tid'];
//            $related['title'] = $att['title'];
//            $related['related_pic'] = Validators::replaceWords($att['related_pic']);
//            $related['url'] = self::getUrl($info['tid'], $os);
//            return $related;
//        } else {
//            return [];
//        }
//    }

    /**
     * 判断稿件是不是存在
     * @param int $tid 稿件id
     */
    public static function checkExist($tid) {
        $info = self::find()->select(['id'])->where(['tid' => $tid])->andWhere(" status >= :online  ", [':online' => 8])->andWhere(" online_time < :time ", ['time' => time()])->one();
        if ($info) {
            return $info['id'];
        } else {
            return FALSE;
        }
    }

    /**
     * 获取主键的id
     * @param type $tid
     */
    public static function getArticleId($tid) {
        $info = self::find()->where(['tid' => $tid])->select(['id'])->one();
        return $info['id'];
    }

    /**
     * 获取详细地址
     * @param  int $tid  
     */
    public static function getUrl($tid, $os = 1,$uid=0) {
        $http = Yii::$app->params['url_host'] ."reader";
        return $http . '?tid=' . $tid.'&os='.$os.'&u='.$uid;
    }

    /**
     * 获取分享的网址
     * @param  int $tid  稿件 id
     */
    public static function getShareUrl($tid,$uid=0) {
        return Yii::$app->params['url_host'] .'share?tid=' . $tid.'&u='.$uid;
    }

}
