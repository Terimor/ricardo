<?php

namespace App\Extensions;

use Illuminate\Cache\RedisStore;
use Illuminate\Contracts\Redis\Factory as Redis;

class SagaRedisStore extends RedisStore
{

    /**
     * Create a new Redis store.
     *
     * @param  \Illuminate\Contracts\Redis\Factory  $redis
     * @param  string  $prefix
     * @param  string  $connection
     * @return void
     */
    public function __construct(Redis $redis, $prefix = '', $connection = 'default')
    {
      parent::__construct($redis, $prefix, $connection);
    }

    /**
     * Serialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function serialize($value)
    {
        //like in Yii2
        return serialize([$value, null]);
    }

    /**
     * Unserialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        $cached_value = unserialize($value);
        //like in Yii2
        return $cached_value[0] ?? null;
    }
}