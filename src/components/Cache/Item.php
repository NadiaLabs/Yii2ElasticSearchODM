<?php

namespace Nadia\ElasticsearchODM\Yii2\components\Cache;

use Psr\Cache\CacheItemInterface;

/**
 * Cache Item for ElasticsearchODM
 */
class Item implements CacheItemInterface
{
    private $key;
    private $value;
    private $isHit;

    /**
     * @var string[]
     */
    private $tags;

    /**
     * @var \DateTime
     */
    private $expiration;

    public function __construct($key, $value = null, $isHit = false, array $tags = [])
    {
        $this->key = $key;
        $this->value = $value;
        $this->isHit = $isHit;
        $this->tags = $tags;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit()
    {
        return $this->isHit;
    }

    public function set($value)
    {
        $this->value = $value;

        return $this;
    }

    public function expiresAt($expiration)
    {
        if (!is_null($expiration) && !($expiration instanceof \DateTime)) {
            throw new \InvalidArgumentException('expiresAt requires \DateTimeInterface or null');
        }

        $this->expiration = $expiration;

        return $this;
    }

    public function expiresAfter($time)
    {
        $date = new \DateTime();

        if (is_numeric($time)) {
            $dateInterval = \DateInterval::createFromDateString(abs($time) . ' seconds');
            if ($time > 0) {
                $date->add($dateInterval);
            } else {
                $date->sub($dateInterval);
            }

            $this->expiration = $date;
        } elseif ($time instanceof \DateInterval) {
            $date->add($time);
            $this->expiration = $date;
        }

        return $this;
    }

    /**
     * 取得快取逾時時間
     *
     * @return \DateTime
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags(array $tags)
    {
        $this->tags = [];

        $this->addTags($tags);

        return $this;
    }

    public function addTag($tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    public function addTags(array $tags)
    {
        foreach ($tags as $tag) {
            $this->tags[] = $tag;
        }

        return $this;
    }
}
