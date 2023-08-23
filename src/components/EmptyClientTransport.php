<?php

namespace Nadia\Yii2ElasticsearchODM\components;

/**
 * Elasticsearch Client Transport for empty hosts
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
