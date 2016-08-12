<?php

use karakum\PathRegistry\PathManager;
use yii\base\InvalidConfigException;
use yii\db\Migration;

class m160606_163120_path_organizer_init extends Migration
{

    /**
     * @throws yii\base\InvalidConfigException
     * @return PathManager
     */
    protected function getPathManager()
    {
        try {
            $pathManager = Yii::$app->pathManager;
        } catch (Exception $e) {
            throw new InvalidConfigException('pathManager instantiate error:' . $e->getMessage());
        }
        if (!$pathManager instanceof PathManager) {
            throw new InvalidConfigException('You should configure "pathManager" component to use database before executing this migration.');
        }
        return $pathManager;
    }

    public function up()
    {
        $pathManager = $this->getPathManager();

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($pathManager->pathTable, [
            'id' => $this->primaryKey(),
            'namespace' => $this->string(32)->notNull(),
            'path' => $this->string(100)->notNull(),
            'size' => $this->integer()->notNull()->defaultValue(0),
            'created' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated' => 'TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP',
        ], $tableOptions);
        $this->createIndex('idx-path_organizer-unique-namespace-path', $pathManager->pathTable, ['namespace', 'path'], true);

    }

    public function down()
    {
        $pathManager = $this->getPathManager();

        $this->dropTable($pathManager->pathTable);
    }
}
