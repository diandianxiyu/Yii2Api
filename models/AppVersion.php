<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_version".
 *
 * @property integer $id
 * @property integer $type
 * @property string $name
 * @property string $url
 * @property string $describe
 * @property integer $status
 * @property integer $ver
 * @property integer $manager
 * @property integer $update_time
 * @property integer $disabled
 */
class AppVersion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app_version';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name', 'url', 'status', 'ver', 'manager', 'update_time'], 'required'],
            [['type', 'status', 'ver', 'manager', 'update_time', 'disabled'], 'integer'],
            [['url', 'describe'], 'string'],
            [['name'], 'string', 'max' => 96]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'type' => '版本类型，安卓为1，iOS为2',
            'name' => '版本名称',
            'url' => '下载地址，iOS为地址，安卓上传安装包',
            'describe' => '版本描述',
            'status' => '是否强制更新',
            'ver' => '版本号',
            'manager' => '管理员id',
            'update_time' => '更新时间',
            'disabled' => '是否禁用',
        ];
    }
    
     /**
     * 版本管理的安卓类型
     */
    const TYPE_ANDROID = 1;
    
    /**
     * 版本管理的iOS类型
     */
    const TYPE_IOS = 2;

    /**
     * disabled 字段中的使用中的值
     */
    const DISABLED_IN_USE =0;

    
    
    /**
     * 获取最新的版本信息
     * @param int $type  版本类型 1安卓 2iOS
     * @param int $lang  语言 cn中文  en英文
     */
    public static function getActiveVersion($type=self::TYPE_ANDROID){
        $info=  self::find()
                ->where("type = :type   and  disabled = :disabled", [':type'=>$type,':disabled'=>self::DISABLED_IN_USE])
                ->select(['name','url','describe','status','ver'])
                ->one();
        if(!$info){
            return FALSE;
        }
        
        $result=[];
        $result['name']=$info['name'];
        $result['url']=$info['url'];
        $result['describe']=$info['describe'];
        $result['status']=$info['status'];
        $result['ver']=$info['ver'];
        return $result;
    }
}
