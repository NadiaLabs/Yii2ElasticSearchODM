<?php

namespace Nadia\ElasticsearchODM\Yii2\commands;

use Nadia\ElasticsearchODM\Yii2\components\ElasticsearchODM;
use ReflectionException;
use yii\base\InvalidConfigException;
use yii\console\Controller;

/**
 * Update Elasticsearch Class Metadata caches
 */
class UpdateClassMetadataCachesController extends Controller
{
    /**
     * Update ElasticSearch Class Metadata caches
     *
     * @throws ReflectionException
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $odm = ElasticsearchODM::make();
        $classMetadataLoader = $odm->getClassMetadataLoader();

        $classMetadataLoader->enableUpdateCache();

        $this->stdout('Update class meta caches:' . "\n");

        foreach ($odm->getDocumentClassNames() as $className) {
            $this->stdout(' - ' . $className . "\n");

            $classMetadataLoader->load($className);
        }
    }
}
