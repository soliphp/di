<?php

namespace Soli\Providers;

use Soli\ServiceProvider;

class RedisServiceProvider extends ServiceProvider
{
    protected $id = 'redis';

    protected $defer = true;

    /**
     * @return \Redis
     * @throws \Exception
     */
    public function register()
    {
        // 获取 redis 配置信息
        $redisConf = $this->config['redis'];

        $client = new \Redis;

        $success = $client->connect($redisConf['host'], $redisConf['port']);

        if (!$success) {
            throw new \Exception("Can't connect to Redis.");
        }

        $client->setOption(\Redis::OPT_PREFIX, $redisConf['prefix']);
        $client->select($redisConf['database']);

        return $client;
    }
}
