<?php

namespace go1\rest\wrapper;

use DI\Container;
use go1\rest\errors\InvalidServiceConfigurationError;
use Memcached;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;
use Redis;
use RuntimeException;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\MemcachedCache;
use Symfony\Component\Cache\Simple\RedisCache;

class CacheClient
{
    private $container;

    public function __construct(Container &$c)
    {
        $this->container = $c;
    }

    public function get(): Psr16CacheInterface
    {
        if (!$this->container->has('cacheConnectionUrl')) {
            throw new InvalidServiceConfigurationError('Missing cache connection URL.');
        }

        $dsn = $this->container->get('cacheConnectionUrl');
        $name = parse_url($dsn, PHP_URL_SCHEME);
        switch ($name) {
            case 'array':
                return new ArrayCache;
            case 'memcached':
                if (!class_exists(Memcached::class)) {
                    throw new RuntimeException('Missing caching driver.');
                }
                $client = MemcachedAdapter::createConnection($dsn, [
                    'compression'          => true,
                    'libketama_compatible' => true,
                ]);

                return new MemcachedCache($client);
            case 'redis':
                if (!class_exists(Redis::class)) {
                    throw new RuntimeException('Missing caching driver.');
                }

                $client = RedisAdapter::createConnection($dsn, [
                    'lazy'           => false,
                    'persistent'     => 0,
                    'persistent_id'  => null,
                    'timeout'        => 30,
                    'read_timeout'   => 0,
                    'retry_interval' => 0,
                ]);

                return new RedisCache($client);
            default:
                throw new RuntimeException('Unsupported backend: ' . $name);
        }
    }
}
