<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "app_derail".
 *
 * @property integer $id
 * @property string $name
 * @property integer $online_status
 * @property integer $status
 */
class AppDerail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'app_derail';
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
            'id' => 'ID',
            'name' => 'Name',
            'online_status' => 'Online Status',
            'status' => 'Status',
        ];
    }
}
