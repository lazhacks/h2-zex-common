<?php

namespace Common\Service;

use Common\Cache\RedisCache;
use Common\Cache\RedisCacheAwareInterface;
use Common\Db\Mapper\MapperInterface;
use Common\Entity\EntityInterface;
use Common\Service\Traits\CreateEntityTrait;
use Zend\Hydrator\ClassMethods;
use Zend\Hydrator\HydratorInterface;

abstract class AbstractService implements
    ServiceInterface,
    RedisCacheAwareInterface
{
    use CreateEntityTrait;

    /**
     * @var MapperInterface
     */
    protected $mapper;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var EntityInterface
     */
    protected $entityPrototype;

    /**
     * @var RedisCache
     */
    protected $cache;

    /**
     * @param MapperInterface $mapper
     * @param EntityInterface $entityPrototype
     * @param HydratorInterface $hydrator
     */
    public function __construct(
        MapperInterface $mapper          = null,
        EntityInterface $entityPrototype = null,
        HydratorInterface $hydrator      = null
    ) {
        if (!$hydrator instanceof HydratorInterface) {
            $hydrator = new ClassMethods();
        }

        $this->mapper          = $mapper;
        $this->entityPrototype = $entityPrototype;
        $this->hydrator        = $hydrator;
    }

    /**
     * @param $id
     * @return object
     * @throws Exception\InvalidArgumentException
     */
    /*public function findById($id)
    {
        $this->requireMapper();

        if ($this->cache instanceof RedisCache && $this->cache->has($id)) {
            return $this->cache->get($id);
        }

        if (!isset($entity) || !$entity instanceof EntityInterface) {
            $entity = $this->mapper->findById($id);

            if (
                $entity instanceof EntityInterface &&
                $entity->isValid() &&
                $this->cache instanceof RedisCache
            ) {
                $this->cache->set($id, $entity);
            }
        }

        return $entity;
    }*/

    /**
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    public function save(EntityInterface $entity)
    {
        if (!$entity instanceof EntityInterface) {
            throw new Exception\InvalidArgumentException(
                'Entity must be an instance of %s',
                EntityInterface::class
            );
        }

        $this->requireMapper();

        echo "IN " . AbstractService::class . PHP_EOL;
        print_r($this->mapper->save($entity));exit;
    }

    /**
     * @return object
     * @throws Exception\InvalidArgumentException
     */
    public function findAll()
    {
        $this->requireMapper();

        return $this->mapper->findAll();
    }

    public function create(EntityInterface $entity)
    {
        $this->requireMapper();

        if (method_exists($this->mapper, 'create')) {
            return $this->mapper->create($entity);
        }

    }

    public function delete(EntityInterface $entity)
    {

    }

    /**
     * @param array $data
     * @throws Exception\BadMethodCallException
     * @return \Zend\Form\Form
     */
    public function createForm(array $data = array())
    {
        throw new Exception\BadMethodCallException(sprintf(
            'Method not implemented %s in %s',
            __function__,
            get_called_class()
        ));
    }

    /**
     * @param RedisCache $cache
     */
    public function setCache(RedisCache $cache)
    {
        $this->cache = $cache;

        $this->cache->setClassName(
            get_called_class()
        );
    }

    /**
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    private function requireMapper()
    {
        if (!$this->mapper instanceof MapperInterface) {
            throw new Exception\InvalidArgumentException(
                'Mapper has not been set.'
            );
        }
    }

    protected function getHydrator()
    {
        return $this->hydrator;
    }
}
