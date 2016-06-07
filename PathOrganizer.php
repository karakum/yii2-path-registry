<?php

namespace karakum\PathRegistry;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "path_organizer".
 *
 * @property integer $id
 * @property string $namespace
 * @property string $path
 * @property integer $size
 * @property integer $created
 * @property integer $updated
 */
class PathOrganizer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return Yii::$app->pathManager->pathTable;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'updated',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['namespace', 'path'], 'required'],
            [['size'], 'integer'],
            [['namespace'], 'string', 'max' => 32],
            [['path'], 'string', 'max' => 255],
            [['created', 'updated'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'namespace' => Yii::t('app', 'Namespace'),
            'path' => Yii::t('app', 'Path'),
            'size' => Yii::t('app', 'Size'),
            'created' => Yii::t('app', 'Created At'),
            'updated' => Yii::t('app', 'Updated At'),
        ];
    }

    public function getFullPath($name = false)
    {
        return Yii::$app->pathManager->getBasePath($this->namespace) . '/' . $this->path . ($name ? '/' . $name : '');
    }

    public function getUrl($name = false)
    {
        return Yii::$app->pathManager->getBaseUrl($this->namespace) . '/' . $this->path . ($name ? '/' . $name : '');
    }
}
