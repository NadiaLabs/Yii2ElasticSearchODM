<?php

namespace Nadia\ElasticsearchODM\Yii2\Tests\components\Cache;

use Nadia\ElasticsearchODM\Yii2\components\Cache\Yii2CacheInterface;

class Yii2Cache implements Yii2CacheInterface
{
    private $items = [];
    private $tags = [];
    private $itemTags = [];

    public function get($key)
    {
        if (array_key_exists($key, $this->items)) {
            $valueExists = true;

            if (!empty($this->itemTags[$key])) {
                foreach ($this->itemTags[$key] as $tag) {
                    if (!array_key_exists($tag, $this->tags)) {
                        $valueExists = false;
                        break;
                    }
                }
            }

            if ($valueExists) {
                return $this->items[$key];
            }
        }

        return null;
    }

    public function set($key, $value, $tags = null, $expire = null)
    {
        $this->items[$key] = $value;

        if (!empty($tags)) {
            if (!is_array($tags)) {
                $tags = [(string) $tags];
            } else {
                $tags = array_values($tags);
            }

            foreach ($tags as $tag) {
                $this->tags[$tag] = true;
            }

            $this->itemTags[$key] = $tags;
        }

        return $this;
    }

    public function delete($key)
    {
        if (array_key_exists($key, $this->items)) {
            unset($this->items[$key]);
        }
        if (array_key_exists($key, $this->itemTags)) {
            unset($this->itemTags[$key]);
        }

        return $this;
    }

    public function invalidateTags($tags = null)
    {
        if (is_null($tags)) {
            $this->items = [];
            $this->itemTags = [];

            return $this;
        }

        if (!is_array($tags)) {
            $tags = [(string) $tags];
        } else {
            $tags = array_values($tags);
        }

        foreach ($tags as $tag) {
            unset($this->tags[$tag]);
        }

        return $this;
    }
}
