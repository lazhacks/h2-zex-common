<?php

namespace Common\Db\Mapper;

use Common\Db\Exception;
use Common\Entity\EntityInterface;
use Common\Collection\CollectionInterface;

interface MapperInterface
{
    /**
     * @param string $entityTable
     * @return MapperInterface
     */
    public function setEntityTable($entityTable);

    /**
     * @param int $id
     * @return MapperInterface
     */
    public function findById($id);

    /**
     * @param array $conditions
     * @return MapperInterface
     */
    public function findAll(array $conditions = array());

    /**
     * @param EntityInterface $entity
     * @return EntityInterface
     */
    public function save(EntityInterface $entity);

    /**
     * @param CollectionInterface $collection
     * @return CollectionInterface
     */
    public function saveAll(CollectionInterface $collection);
}
