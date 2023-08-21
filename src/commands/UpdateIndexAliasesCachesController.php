<?php

namespace Nadia\ElasticsearchODM\Yii2\commands;

use Nadia\ElasticsearchODM\Yii2\components\ElasticsearchODM;
use Psr\Cache\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\console\Controller;

/**
 * Update Elasticsearch Index Aliases caches
 */
class UpdateIndexAliasesCachesController extends Controller
{
    /**
     * Update Elasticsearch Index Aliases caches
     *
     * @throws InvalidArgumentException
     * @throws InvalidConfigException
     */
    public function actionIndex()
    {
        $indexNameProvider = ElasticsearchODM::make()->getIndexNameProvider();

        $indexNameProvider->enableRefreshIndexAliasesCache();

        $this->stdout('Update index aliases caches...');

        $indexNameProvider->loadValidIndexNames();

        $this->stdout('...done!' . "\n");
    }
}
