<?php

namespace Laracasts\Matryoshka;

use Exception;
use Illuminate\Cache\Repository;

class BladeDirective
{
    /**
     * The config instance
     *
     * @var array
     */
    protected $config;

    /**
     * The cache instance.
     *
     * @var RussianCaching
     */
    protected $cache;

    /**
     * A list of model cache keys.
     *
     * @param array $keys
     */
    protected $keys = [];

    /**
     * Create a new instance.
     *
     * @param array          $config
     * @param RussianCaching $cache
     */
    public function __construct(array $config, Repository $cache)
    {
        $this->config = $config;

        // Create caching instance
        $this->cache = new RussianCaching(
            $cache,
            $this->config['cache_tags'],
            $this->config['cache_expires']
        );
    }

    /**
     * Handle the @cache setup.
     *
     * @param mixed       $model
     * @param string|null $key
     */
    public function setUp($model, $key = null)
    {
        ob_start();

        $this->keys[] = $key = $this->normalizeKey($model, $key);

        return $this->cache->has($key);
    }

    /**
     * Handle the @endcache teardown.
     */
    public function tearDown()
    {
        return $this->cache->put(
            array_pop($this->keys), ob_get_clean()
        );
    }

    /**
     * Normalize the cache key.
     *
     * @param mixed      $item
     * @param array|null $options
     */
    protected function normalizeKey($item, $options = null)
    {
        // If the user wants to provide their own cache
        // key, we'll opt for that.
        if (is_string($item)) {
            $key = $item;
        }

        // Otherwise we'll try to use the item to calculate
        // the cache key, itself.
        if (is_object($item) && method_exists($item, 'getCacheKey')) {
            $key = $item->getCacheKey();
        }

        // If we're dealing with a collection, we'll
        // use a hashed version of items updated_at field.
        if ($item instanceof \Illuminate\Support\Collection) {
            $key = md5($item->pluck('updated_at'));
        }

        if (!isset($key)) {
            throw new Exception('Could not determine an appropriate cache key.');
        }

        // If additional options are passed, we'll
        // add them to the key.
        if (is_array($options)) {
            $key .= implode('.', $options);
        }

        return $key;
    }
}
