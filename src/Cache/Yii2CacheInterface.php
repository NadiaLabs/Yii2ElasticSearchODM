<?php

namespace Nadia\ElasticSearchODM\Yii2\Cache;

interface Yii2CacheInterface
{
    public function get($key);

    public function set($key, $value, $tags = null, $expire = null);

    public function delete($key);

    public function invalidateTags($tags = null);
}
