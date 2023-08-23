<?php

namespace Nadia\Yii2ElasticsearchODM\commands;

use Nadia\Yii2ElasticsearchODM\components\Yii2ElasticsearchODM;
use ReflectionException;
use yii\base\InvalidConfigException;
use yii\console\Controller;

/**
 * Update Elasticsearch Class Metadata caches
 */
class UpdateClassMetadataCachesController extends Controller
{
    /**
     * Update Elasticsearch Class Metadata caches
     *
     * @throws ReflectionException
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $odm = Yii2ElasticsearchODM::make();
        $classMetadataLoader = $odm->getClassMetadataLoader();

        $classMetadataLoader->enableUpdateCache();

        $this->stdout('Update class meta caches:' . "\n");

        foreach ($odm->getDocumentClassNames() as $className) {
            $this->stdout(' - ' . $className . "\n");

            $classMetadataLoader->load($className);
        }
    }
}
