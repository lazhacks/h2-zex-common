<?php

namespace Common\Db\Traits;

use Common\Db\Exception;
use Common\Db\Mapper\MapperInterface;
use Common\Entity\EntityInterface;
use Common\Service\ServiceInterface;
use Zend\Cache\Storage\StorageInterface;

/**
 * Find Entity Trait
 *
 * Attempts to locate a resource by id from the associated mapper.
 * If no record is found, an empty entity is returned.
 */
trait FindByIdTrait
{
    /**
     * @param mixed $id
     * @return EntityInterface
     * @throws Exception\BadMethodCallException
     */
    public function findById($id)
    {
        if (!in_array(ServiceInterface::class, class_implements($this))) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s must implement %s',
                __trait__,
                ServiceInterface::class
            ));
        }

        $cache = $this->getCache();
        if ($cache instanceof StorageInterface && $cache->has($id)) {
            return $cache->get($id);
        }

        $entity = $this->getMapper()->findById($id);
        if ($cache instanceof StorageInterface) {
            $cache->set($id, $entity);
        }

        return $entity;
    }

    /**
     * @return null|StorageInterface
     */
    private function getCache()
    {
        if (isset($this->disableCache) && $this->disableCache === true) {
            return null;
        }

        if (!isset($this->cache) || $this->cache instanceof StorageInterface) {
            return null;
        }

        return $this->cache;
    }

    /**
     * @return MapperInterface
     * @throws Exception\BadMethodCallException
     */
    private function getMapper()
    {
        if (!isset($this->mapper) || !$this->mapper instanceof MapperInterface) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s requires a mapper that implements %s',
                __trait__,
                MapperInterface::class
            ));
        }

        if (!method_exists($this->mapper, 'findById')) {
            throw new Exception\BadMethodCallException(sprintf(
                'Method findById() not found in %s',
                get_class($this->mapper)
            ));
        }

        return $this->mapper;
    }
}
