<?php

namespace Nadia\ElasticsearchODM\Yii2\components;

/**
 * ElasticSearch Client Transport for empty hosts
 */
class EmptyClientTransport
{
    public function performRequest($method, $uri)
    {
        return [];
    }

    public function resultOrFuture($result, $options = [])
    {
        return [];
    }
}
