<?php

namespace app\models;

use Yii;
use app\models\UserAvatar;
use app\models\UserAccount;

/**
 * This is the model class for table "article_comments".
 *
 * @property integer $id
 * @property integer $article_id
 * @property integer $user_id
 * @property integer $create_time
 * @property integer $disabled
 * @property string $contents
 */
class ArticleComments extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article_comments';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article_id', 'user_id', 'create_time', 'disabled', 'contents'], 'required'],
            [['article_id', 'user_id', 'create_time', 'disabled'], 'integer'],
            [['contents'], 'string', 'max' => 420]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'article_id' => '稿件id',
            'user_id' => '用户id',
            'create_time' => '创建的时间',
            'disabled' => '删除状态',
            'contents' => '评论的文字',
        ];
    }
    
    /**
     * 创建一个弹幕
     * @param int $article_id  稿件的主键id
     * @param int $user_id   用户的主键id
     * @param string $contents   弹幕的内容
     */
    public static function addComment($article_id,$user_id,$contents){
        $model=new ArticleComments();
        $model->article_id=$article_id;
        $model->user_id=$user_id;
        $model->disabled=0;
        $model->create_time=  time();
        $model->contents= $contents;
        if($model->save()){
            return $model->id;
        }else{
            return FALSE;
        }
    }
    
    /**
     * 获取弹幕的信息
     * @param type $id
     */
    public static function getOneComment($id){
        $info=self::find()->where(['id'=>$id])->andWhere(['disabled'=>0])->select(['article_id','user_id','create_time','contents'])->one();
        if($info){
            //格式化输出
//            $comment['article_id']=$info['article_id'];
            $comment['uid']=UserAccount::getUid($info['user_id']);
            $comment['create_time']=$info['create_time'];
            $comment['contents']=  $info['contents'];
            $comment['avatar']=  UserAvatar::get($info['user_id']);
            return $comment;
        }else{
            return FALSE;
        }
    }
    
    /**
     * 获取弹幕列表
     * @param int $article_id 稿件id
     * @param int $time 时间的限制
     * @param int $count 获取的数量
     * @param int $page 页码
     */ 
    public static function getList($article_id,$time,$count,$page){
        $list=self::find()->where(['article_id'=>$article_id])
                ->andWhere(["disabled"=>0])
                ->select(['user_id','create_time','contents'])
                ->andWhere(" create_time <= :time",[':time'=>$time])
                ->limit($count)
                ->offset($count*$page)
                ->orderBy(['create_time'=>SORT_DESC])->all();
        if($list){
            //组装
            $arr=[];
            foreach ($list as  $value) {
                $att=$value->attributes;
                $one=[];
                $one['uid']=  UserAccount::getUid($att['user_id']);
                $one['create_time']=$att['create_time'];
                $one['contents']=  $att['contents'];
                $one['avatar']=  UserAvatar::get($att['user_id']);
                $arr[]=$one;
            }
            return $arr;
        }else{
            return [];
        }        
    }
    
    
    /**
     * 获取弹幕列表
     * @param int $article_id 稿件id
     * @param int $time 时间的限制
     * @param int $count 获取的数量
     * @param int $page 页码
     */ 
    public static function getNextPage($article_id,$time,$count,$page){
        $page++;
        $list=self::find()->where(['article_id'=>$article_id])
                ->andWhere(["disabled"=>0])
                ->select(['id'])
                ->andWhere(" create_time <= :time",[':time'=>$time])
                ->limit($count)
                ->offset($count*$page)
                ->orderBy(['create_time'=>SORT_DESC])->all();
        if($list){
            //组装
            return TRUE;
        }else{
            return FALSE;
        }        
    }
}
