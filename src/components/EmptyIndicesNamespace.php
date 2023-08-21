<?php

namespace Nadia\ElasticsearchODM\Yii2\components;

/**
 * ElasticSearch IndicesNamespace for empty hosts
 */
class EmptyIndicesNamespace
{
    public function getAliases()
    {
        return [];
    }

    public function create($params)
    {
        return [];
    }

    public function exists($params)
    {
        return false;
    }

    public function putTemplate($params)
    {
        return [];
    }
}
