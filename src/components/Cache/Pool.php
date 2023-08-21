<?php

namespace Nadia\ElasticsearchODM\Yii2\components\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Cache Pool for ElasticSearchODM
 */
class Pool implements CacheItemPoolInterface
{
    /** @var Item[] */
    private $deferredItems = [];

    /**
     * @var Yii2CacheInterface
     */
    private $cache;

    public function __construct(Yii2CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     *
     * @return Item
     */
    public function getItem($key)
    {
        if (isset($this->deferredItems[$key])) {
            $this->commit();
        }

        $value = $this->cache->get($key);

        if ($value) {
            return new Item($key, $value, true);
        }

        return new Item($key, null, false);
    }

    /**
     * {@inheritdoc}
     *
     * @return Item[]
     */
    public function getItems(array $keys = [])
    {
        $items = [];

        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }

        return $items;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        if (isset($this->deferredItems[$key])) {
            $this->commit();
        }

        return false !== $this->cache->get($key);
    }

    public function clear()
    {
        $this->deferredItems = [];

        return true;
    }

    public function deleteItem($key)
    {
        unset($this->deferredItems[$key]);

        return $this->cache->delete($key);
    }

    public function deleteItems(array $keys)
    {
        $success = true;

        foreach ($keys as $key) {
            $success = $this->deleteItem($key) && $success;
        }

        return $success;
    }

    public function deleteItemByTag($tag)
    {
        return $this->cache->invalidateTags($tag);
    }

    public function deleteItemByTags(array $tags)
    {
        return $this->cache->invalidateTags($tags);
    }

    public function save(CacheItemInterface $item)
    {
        return $this->saveDeferred($item) && $this->commit();
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        if ($item instanceof Item) {
            $this->deferredItems[$item->getKey()] = $item;

            return true;
        }

        return false;
    }

    public function commit()
    {
        if (empty($this->deferredItems)) {
            return true;
        }

        $now = microtime(true);
        $validKeys = [];
        $expiredKeys = [];

        foreach ($this->deferredItems as $key => $item) {
            $lifetime = ($item->getExpiration() ? $item->getExpiration()->getTimestamp() : $now) - $now;

            if ($lifetime < 0) {
                $expiredKeys[] = $key;
                continue;
            }

            $validKeys[$lifetime][$key] = $item;
        }

        $this->deferredItems = [];

        $this->deleteItems($expiredKeys);

        /** @var Item[] $items */
        $success = true;
        foreach ($validKeys as $lifetime => $items) {
            foreach ($items as $key => $item) {
                if ($lifetime > 0) {
                    $success = $this->cache->set($key, $item->get(), $item->getTags(), $lifetime) && $success;
                } else {
                    $success = $this->cache->set($key, $item->get(), $item->getTags()) && $success;
                }
            }
        }

        return $success;
    }
}
