<?php

namespace Common\Cache;

use Zend\Cache\Storage\StorageInterface;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RedisCacheInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof RedisCacheAwareInterface) {
            /** @var StorageInterface $cache */
            $cache = $serviceLocator->get('Redis');
            $redis = new RedisCache($cache);

            $instance->setCache($redis);
        }
    }
}
