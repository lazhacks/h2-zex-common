<?php

namespace Common\Service\Traits;

use Common\Entity\EntityInterface;
use Common\Service\Exception;
use Common\Service\ServiceInterface;
use Zend\Hydrator\HydratorInterface;

/**
 * Create Entity Trait
 *
 * Allows for creating a hydrated entity by optionally passing data.
 * An empty entity will be returned if not data is passed.
 *
 * Can only be used within the scope of a service.
 */
trait CreateEntityTrait
{
    /**
     * @param array $data
     * @return EntityInterface
     * @throws Exception\BadMethodCallException
     */
    protected function createEntity(array $data = array())
    {
        if (!in_array(ServiceInterface::class, class_implements($this))) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s must implement %s',
                __trait__,
                ServiceInterface::class
            ));
        }

        $entity = clone $this->getEntityPrototype();

        if (is_array($data) && !empty($data)) {
            $entity = $this->getHydrator()->hydrate($data, $entity);
        }

        return $entity;
    }

    /**
     * @return EntityInterface
     * @throws Exception\BadMethodCallException
     */
    private function getEntityPrototype()
    {
        if (!isset($this->entityPrototype) || !$this->entityPrototype instanceof EntityInterface) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s requires an entity prototype that implements %s',
                __trait__,
                EntityInterface::class
            ));
        }

        return $this->entityPrototype;
    }

    /**
     * @return HydratorInterface
     * @throws Exception\BadMethodCallException
     */
    protected function getHydrator()
    {
        if (!isset($this->hydrator) || !$this->hydrator instanceof HydratorInterface) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s requires a hydrator that implements %s',
                __trait__,
                HydratorInterface::class
            ));
        }

        return $this->hydrator;
    }
}
