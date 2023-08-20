<?php

namespace Nadia\ElasticSearchODM\Yii2;

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
