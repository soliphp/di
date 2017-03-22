<?php

namespace Soli\Providers;

use Soli\ServiceProvider;

class RedisServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return \Redis
     */
    public function register()
    {
        // 获取 redis 配置信息
        $redisConf = $this->config['redis'];

        $client = new \Redis;

        $success = $client->connect($redisConf['host'], $redisConf['port']);

        $client->setOption(\Redis::OPT_PREFIX, $redisConf['prefix']);
        $client->select($redisConf['database']);

        return $client;
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['redis', 'redis.connection'];
    }
}
