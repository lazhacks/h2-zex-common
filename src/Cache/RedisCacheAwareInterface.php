<?php

namespace Common\Cache;

interface RedisCacheAwareInterface
{
    public function setCache(RedisCache $cache);
}
