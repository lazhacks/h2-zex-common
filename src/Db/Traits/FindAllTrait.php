<?php

namespace Common\Db\Traits;

use Common\Db\Exception;
use Common\Entity\EntityInterface;
use Common\Service\ServiceInterface;

/**
 * Find Entity Trait
 *
 * Attempts to locate a resource by id from the associated mapper.
 * If no record is found, an empty entity is returned.
 */
trait FindAllTrait
{
    /**
     * @param array $conditions
     * @return EntityInterface
     * @throws Exception\BadMethodCallException
     */
    public function findAll(array $conditions = array())
    {
        if (!in_array(ServiceInterface::class, class_implements($this))) {
            throw new Exception\BadMethodCallException(sprintf(
                '%s must implement %s',
                __trait__,
                ServiceInterface::class
            ));
        }

        return $this->getMapper()->findAll($conditions);
    }
}
