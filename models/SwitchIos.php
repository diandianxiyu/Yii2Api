<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%switch_ios}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $online_status
 * @property integer $status
 */
class SwitchIos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%switch_ios}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['online_status', 'status'], 'integer'],
            [['name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'online_status' => Yii::t('app', 'Online Status'),
            'status' => Yii::t('app', 'Status'),
        ];
    }


}
