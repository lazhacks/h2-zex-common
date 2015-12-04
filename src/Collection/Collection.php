<?php

namespace Common\Collection;

use Countable;
use ArrayIterator;
use IteratorAggregate;
use Common\Entity\EntityInterface;

class Collection implements
    Countable,
    IteratorAggregate,
    CollectionInterface
{
    /**
     * @var array
     */
    protected $entities = array();

    /**
     * @return int
     */
    public function count()
    {
        return count($this->entities);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->entities);
    }

    /**
     * @param EntityInterface $entity
     * @return CollectionInterface
     */
    public function add(EntityInterface $entity)
    {
        $this->entities[] = $entity;

        return $this;
    }
}
