<?php

namespace App\Extensions;

use Illuminate\Cache\RedisStore;
use Illuminate\Contracts\Redis\Factory as Redis;

class MongoStore extends RedisStore
{
   public function __construct(Redis $redis, $prefix = '', $connection = 'default')
   {
       parent::__construct($redis, $prefix, $connection);
   }
}
