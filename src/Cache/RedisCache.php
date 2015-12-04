<?php

namespace Common\Cache;

use Zend\Cache\Storage\StorageInterface;

class RedisCache
{
    /**
     * @var StorageInterface
     */
    private $cache;

    /**
     * Fully Qualified Class Name
     * @var string
     */
    private $fqcn;

    /**
     * @param StorageInterface $cache
     */
    public function __construct(StorageInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param mixed$key
     * @return bool
     */
    public function has($key)
    {
        if (!$this->isKey($key)) {
            $key = $this->createKey($key);
        }

        return $this->cache->hasItem($key);
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    public function get($key)
    {
        if (!$this->isKey($key)) {
            $key = $this->createKey($key);
        }

        return unserialize($this->cache->getItem($key));
    }

    /**
     * @param mixed $key
     * @param mixed $data
     * @return bool
     */
    public function set($key, $data)
    {
        if (!$this->isKey($key)) {
            $key = $this->createKey($key);
        }

        $data = serialize($data);

        return $this->cache->setItem($key, $data);
    }

    /**
     * @param mixed $key
     * @return bool
     */
    public function remove($key)
    {
        if (!$this->isKey($key)) {
            $key = $this->createKey($key);
        }

        return $this->cache->removeItem($key);
    }

    /**
     * @param mixed $key
     * @return string
     */
    public function createKey($key)
    {
        return sprintf('%s::%s', $this->fqcn, $key);
    }

    /**
     * @param string $fqcn - Fully Qualified Class Name
     */
    public function setClassName($fqcn)
    {
        $this->fqcn = $fqcn;
    }

    /**
     * @param mixed $key
     * @return bool
     */
    private function isKey($key)
    {
        return stripos($key, $this->fqcn) > -1;
    }
}
