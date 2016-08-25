<?php

/**
 * @copyright Copyright &copy; Shertsinger Andrey, 2016
 * @package yii2-files-registry
 * @version 0.1.0
 */

namespace karakum\PathRegistry;

use RuntimeException;
use Yii;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\helpers\Url;


/**
 * Class PathManager
 *
 *
 *
 * Add component to
 * ```
 *     'components' => [
 *     ...
 *         'pathManager' => [
 *             'class' => 'karakum\PathRegistry\PathManager',
 *             'defaultMaxFiles' => 1000,
 *             'defaultNewFolders' => 20,
 *             'namespaces' => [
 *                 'avatar' => [
 *                     'path' => '@webroot/upload/avatar',
 *                     'url' => '@web/upload/avatar',
 *                     'maxFiles' => 100,
 *                     'newFolders' => 5
 *                 ],
 *             ],
 *         ],
 *     ...
 *     ],
 * ```
 *
 * @package karakum\FilesRegistry
 * @author Shertsinger Andrey <andrey@shertsinger.ru>
 */
class PathManager extends Component
{

    /**
     * @var string the name of the table storing path items. Defaults to "path_organizer".
     */
    public $pathTable = '{{%path_organizer}}';

    public $defaultMaxFiles = 1000;
    public $defaultNewFolders = 20;
    public $namespaces;

    public function __construct($config = [])
    {

        parent::__construct($config);
    }

    public function init()
    {
        parent::init();

    }

    /**
     * @return mixed
     */
    public function getDefaultMaxFiles()
    {
        return $this->defaultMaxFiles;
    }

    public function getStorage($_namespace)
    {
        if (!isset($this->namespaces[$_namespace])) {
            throw new InvalidParamException('Path namespace "' . $_namespace . '" not found');
        }

        $ns = $this->namespaces[$_namespace];
        $maxFiles = isset($ns['maxFiles']) ? $ns['maxFiles'] : $this->defaultMaxFiles;
        $newFolders = isset($ns['newFolders']) ? $ns['newFolders'] : $this->defaultNewFolders;
        $nsPath = Yii::getAlias($ns['path']);
        $nsUrl = $ns['url'];

        return [
            'maxFiles' => $maxFiles,
            'newFolders' => $newFolders,
            'path' => $nsPath,
            'url' => $nsUrl,
        ];
    }

    public function getBasePath($_namespace, $path = false)
    {
        $ns = $this->getStorage($_namespace);
        return $ns['path'] . ($path ? '/' . $path : '');
    }

    public function getBaseUrl($_namespace, $path = false, $scheme = false)
    {
        $ns = $this->getStorage($_namespace);
        if (is_array($ns['url'])) {
            throw new RuntimeException("Requested namespace '$_namespace' has no direct link");
        }
        return Url::to($ns['url'] . ($path ? '/' . $path : ''), $scheme);
    }

    public function isDirectLink($_namespace)
    {
        $ns = $this->getStorage($_namespace);
        return !is_array($ns['url']);
    }

    public function getUrl($_namespace, $path, $params, $scheme = false)
    {
        $ns = $this->getStorage($_namespace);
        if (is_array($ns['url'])) {
            return Url::to(array_merge($ns['url'], $params), $scheme);
        }
        return Url::to($ns['url'] . ($path ? '/' . $path : ''), $scheme);
    }

    public function getPath($_namespace)
    {
        $ns = $this->getStorage($_namespace);

        $maxFiles = $ns['maxFiles'];
        $newFolders = $ns['newFolders'];
        $nsPath = $ns['path'];

        $query = PathOrganizer::find();
        $query->andWhere(['namespace' => $_namespace]);
        $query->andWhere(['<', 'size', $maxFiles]);

        $pathObj = $query->orderBy('size')->one();
        if (!$pathObj) {
            $transaction = Yii::$app->getDb()->beginTransaction();

            $newDirs = [];
            $tryCnt = $newFolders;
            while ($newFolders && $tryCnt) {

                $rnd = md5(uniqid());
                if (!file_exists($nsPath . '/' . $rnd)) {
                    if (@mkdir($nsPath . '/' . $rnd, 0777, true)) {
                        $newDirs[] = $nsPath . '/' . $rnd;
                        $pathObj = new PathOrganizer([
                            'namespace' => $_namespace,
                            'path' => $rnd,
                        ]);
                        if ($pathObj->save()) {
                            $newFolders--;
                        } else {
                            $tryCnt--;
                        }
                    } else {
                        $tryCnt--;
                    }
                }
            }

            if ($newFolders == 0) {
                $transaction->commit();
            } else {
                $transaction->rollBack();

                foreach ($newDirs as $dir) {
                    if (!@rmdir($dir)) {
                        Yii::error('Dir \'' . $dir . '\' does not removed');
                    }
                }
                throw new RuntimeException('Folders generation error');
            }

        }
        return $pathObj;
    }

    /**
     * Увеличивает кол-во использований на 1
     *
     * @param PathOrganizer $path
     */
    public function countUpPath($path)
    {
        $path->updateCounters(['size' => 1]);
    }

    /**
     * Уменьшает кол-во использований на 1
     *
     * @param PathOrganizer $path
     */
    public function countDownPath($path)
    {
        $path->updateCounters(['size' => -1]);
    }

    public function getPathByFile($_namespace, $filepath)
    {
        $query = PathOrganizer::find();
        $query->andWhere(['namespace' => $_namespace]);
        $query->andWhere(['path' => dirname($filepath)]);
        $pathObj = $query->orderBy('size')->one();
        return $pathObj;
    }

    public function countDownFile($_namespace, $filepath)
    {
        $pathObj = $this->getPathByFile($_namespace, $filepath);
        if ($pathObj) {
            $this->countDownPath($pathObj);
        }
    }


}