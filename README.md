Path organizer Extension for Yii 2
==================================
This extension provides a directory generator for file storage based on the maximum occupancy directory.

Each time calling `getPath` method it search most empty directory. When all directories full filled according
with max files count per directory(default 1000 files), extension generate new portion of directories(default 20 dirs).
Using directory with each file must be marked by the method `countUpPath`.
When file removed from directory the method `countDownPath` must be called.

Different filling setting can be using with several namespaces.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist karakum/yii2-path-registry "*"
```

or add

```
"karakum/yii2-path-registry": "*"
```

to the require section of your `composer.json` file.

Configure application component

```
    'components' => [
    ...
        'pathManager' => [
            'class' => 'karakum\PathRegistry\PathManager',
            'defaultMaxFiles' => 1000,
            'defaultNewFolders' => 20,
            'namespaces' => [
                'avatar' => [
                    'path' => '@webroot/uploads/avatar',
                    'url' => '@web/uploads/avatar',
                    'maxFiles' => 100,
                    'newFolders' => 5
                ],
            ],
        ],
    ...
    ],
```

Create migration:
```
$ yii migrate/create path_organizer
```

and modify like:

```php
<?php

use yii\db\Migration;

require(Yii::getAlias('@karakum/PathRegistry/migrations/m160606_163120_path_organizer_init.php'));

class mXXXXXX_XXXXXX_path_organizer extends Migration
{
    public function up()
    {
        $path = new m160606_163120_path_organizer_init();
        $path->up();
    }

    public function down()
    {
        $path = new m160606_163120_path_organizer_init();
        $path->down();
    }
}```

Migration will create table with default name `{{%path_organizer}}`. To change it use property `pathTable` of PathManager component.

Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?=

$directory = Yii::$app->pathManager->getPath('avatar')->getFullPath();           //  '/var/www/yii2-app/web/uploads/avatar/7b08aea20ff07411b74b97ebe7fe6bf8'
$filename = Yii::$app->pathManager->getPath('avatar')->getFullPath('photo.png'); //  '/var/www/yii2-app/web/uploads/avatar/7b08aea20ff07411b74b97ebe7fe6bf8/photo.png'
$imgSrc = Yii::$app->pathManager->getPath('avatar')->getUrl('photo.png');        //  '/uploads/avatar/7b08aea20ff07411b74b97ebe7fe6bf8/photo.png'

?>```