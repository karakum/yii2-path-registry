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
     * @return PathManager
     */
    private static function getPathManager()
    {
        return Yii::$app->pathManager;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return self::getPathManager()->pathTable;
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

    public function getBasePath($name = false)
    {
        return self::getPathManager()->getBasePath($this->namespace) . ($name ? '/' . $name : '');
    }

    public function getFullPath($name = false)
    {
        return self::getPathManager()->getBasePath($this->namespace) . '/' . $this->path . ($name ? '/' . $name : '');
    }

    public function getUrl($file, $params, $scheme = false)
    {
        $pm = self::getPathManager();
        if ($pm->isDirectLink($this->namespace)) {
            return $pm->getBaseUrl($this->namespace, $file, $scheme);
        } else {
            return $pm->getUrl($this->namespace, $file, $params, $scheme);
        }
    }
}
