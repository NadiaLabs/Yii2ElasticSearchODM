<?php

namespace Nadia\Yii2ElasticsearchODM\Tests;

use Nadia\Yii2ElasticsearchODM\Module;
use Nadia\Yii2ElasticsearchODM\Tests\components\Cache\Yii2Cache;
use yii\helpers\ArrayHelper;

class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function mockApplication($config = [], $appClass = '\yii\console\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
        ], $config));
    }

    protected function mockWebApplication($config = [], $appClass = '\yii\web\Application')
    {
        $cacheDir = $this->getCacheDir();

        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
            'aliases' => [
                '@bower' => '@vendor/bower-asset',
                '@npm' => '@vendor/npm-asset',
            ],
            'modules' => [
                'ElasticsearchODM' => [
                    'class' => Module::class,
                ],
            ],
            'components' => [
                'request' => [
                    'cookieValidationKey' => 'wefJDF8sfdsfSDefwqdxj9oq',
                    'scriptFile' => __DIR__ . '/index.php',
                    'scriptUrl' => '/index.php',
                    'isConsoleRequest' => false,
                ],
                'ElasticsearchODM' => [
                    'class' => 'Nadia\Yii2ElasticsearchODM\components\Yii2ElasticsearchODM',
                    'hosts' => [],
                    'indexNamePrefix' => 'dev',
                    'documentDirs' => [
                        'Nadia\Yii2ElasticsearchODM\Tests\components\Document\\' => __DIR__ . '/components/Document',
                    ],
                    'cacheDir' => $cacheDir,
                    'cache' => new Yii2Cache(),
                ],
            ],
        ], $config));
    }

    protected function destroyApplication()
    {
        if (\Yii::$app && \Yii::$app->has('session', true)) {
            \Yii::$app->session->close();
        }
        \Yii::$app = null;
    }

    protected function getVendorPath()
    {
        $vendor = dirname(dirname(__DIR__)) . '/vendor';
        if (!is_dir($vendor)) {
            $vendor = dirname(dirname(dirname(dirname(__DIR__))));
        }

        return $vendor;
    }

    protected function clearCacheDir()
    {
        $cacheDir = $this->getCacheDir();

        foreach (scandir($cacheDir) as $filename) {
            if (is_file($cacheDir . '/' . $filename)) {
                unlink($cacheDir . '/' . $filename);
            }
        }
    }

    protected function getCacheDir()
    {
        $cacheDir = __DIR__ . '/.cache';

        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        return $cacheDir;
    }
}
