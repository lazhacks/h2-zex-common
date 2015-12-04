<?php

namespace Common\Service\Traits;

use Common\Service\Exception;
use Common\Entity\EntityInterface;
use Common\Collection\CollectionInterface;
use Common\Service\ServiceInterface;

/**
 * Create Collection Trait
 *
 * Allows for creating a collection by optionally passing data. If array
 * data is provided, an entity will be created and added to the collection.
 * An empty collection will be returned if not data is passed.
 *
 * Can only be used within the scope of a service.
 */
trait CreateCollectionTrait
{
    /**
     * Traits
     */
    use CreateEntityTrait;

    /**
     * @param array $data
     * @return EntityInterface
     */
    protected function createCollection(array $data = array())
    {
        if (!in_array(ServiceInterface::class, class_implements($this))) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s must implement interface %s',
                __trait__,
                ServiceInterface::class
            ));
        }

        /** @var CollectionInterface $collection */
        $collection = clone $this->getCollectionPrototype();

        if (is_array($data) && !empty($data)) {
            foreach ($data as $entity) {
                if (!$entity instanceof EntityInterface) {
                    $entity = $this->createEntity($entity);
                }

                $collection->add($entity);
            }
        }

        return $collection;
    }

    /**
     * @return CollectionInterface
     * @throws Exception\BadMethodCallException
     */
    private function getCollectionPrototype()
    {
        if (!isset($this->collectionPrototype) || !$this->collectionPrototype instanceof CollectionInterface) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s requires a collection prototype that implements %s',
                __trait__,
                CollectionInterface::class
            ));
        }

        return $this->collectionPrototype;
    }
}
