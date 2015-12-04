<?php

namespace Common\Collection;

use Common\Entity\EntityInterface;

interface CollectionInterface
{
    /**
     * @param EntityInterface $entity
     * @return CollectionInterface
     */
    public function add(EntityInterface $entity);
}
