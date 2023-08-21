<?php

namespace Nadia\ElasticsearchODM\Yii2\components;

/**
 * ElasticSearch Client for empty hosts
 */
class EmptyClient
{
    public $transport;

    public $indicesNamespace;

    public function __construct()
    {
        $this->transport = new EmptyClientTransport();
        $this->indicesNamespace = new EmptyIndicesNamespace();
    }

    public function search($params = array())
    {
        return [];
    }

    public function msearch($params = array())
    {
        return [];
    }

    public function indices()
    {
        return $this->indicesNamespace;
    }

    public function index($params)
    {
        return [];
    }

    public function update($params)
    {
        return [];
    }

    public function bulk($params)
    {
        return [];
    }

    public function getValidIndexNames($indexNames)
    {
        return $indexNames;
    }

    public function loadValidIndexNames()
    {
        return $this;
    }

    public function enableRefreshCache()
    {
        return $this;
    }

    public function disableRefreshCache()
    {
        return $this;
    }
}
