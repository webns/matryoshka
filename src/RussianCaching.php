<?php

namespace Laracasts\Matryoshka;

use Illuminate\Cache\Repository as Cache;

class RussianCaching
{
    /**
     * Instance of cache manager.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Lifetime of the cache.
     *
     * @var int
     */
    protected $expires;

    /**
     * Create a new class instance.
     *
     * @param Cache        $cache
     * @param array|null   $tags
     * @param int          $expires
     */
    public function __construct(Cache $cache, $tags = null, $expires = 60)
    {
        $this->cache = $tags ? $cache->tags($tags) : $cache;
        $this->expires = $expires;
    }

    /**
     * Put to the cache.
     *
     * @param mixed  $key
     * @param string $fragment
     */
    public function put($key, $fragment)
    {
        $key = $this->normalizeCacheKey($key);

        return $this->cache
            ->remember($key, $this->expires, function () use ($fragment) {
                return $fragment;
            });
    }

    /**
     * Check if the given key exists in the cache.
     *
     * @param mixed $key
     */
    public function has($key)
    {
        $key = $this->normalizeCacheKey($key);

        return $this->cache->has($key);
    }

    /**
     * Normalize the cache key.
     *
     * @param mixed $key
     */
    protected function normalizeCacheKey($key)
    {
        if (is_object($key) && method_exists($key, 'getCacheKey')) {
            return $key->getCacheKey();
        }

        return $key;
    }
}
