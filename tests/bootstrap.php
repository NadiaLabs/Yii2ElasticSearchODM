<?php

// ensure we get report on all possible php errors
error_reporting(-1);

define('YII_ENABLE_ERROR_HANDLER', false);
define('YII_DEBUG', true);
define('YII_ENV', 'test');

$_SERVER['SCRIPT_NAME'] = '/' . __DIR__;
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

// require composer autoloader if available
$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('@yiiunit', __DIR__);

if (getenv('TEST_RUNTIME_PATH')) {
    Yii::setAlias('@yiiunit/runtime', getenv('TEST_RUNTIME_PATH'));
    Yii::setAlias('@runtime', getenv('TEST_RUNTIME_PATH'));
}

require_once __DIR__ . '/compatibility.php';
require_once __DIR__ . '/TestCase.php';
