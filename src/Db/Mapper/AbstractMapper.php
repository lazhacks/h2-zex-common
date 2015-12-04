<?php

namespace Common\Db\Mapper;

use Common\Db\Exception;
use Common\Collection\Collection;
use Common\Collection\CollectionInterface;
use Common\Entity\EntityInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\Adapter\Driver\StatementInterface;
use Zend\Db\Sql\TableIdentifier;
use Zend\Hydrator\HydratorInterface;
use Zend\Hydrator\ClassMethods;

abstract class AbstractMapper
{
    /**
     * @var Adapter
     */
    private $adapter;

    /**
     * @var Adapter
     */
    private $slaveAdapter;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var EntityInterface
     */
    private $entityPrototype;

    /**
     * @var CollectionInterface
     */
    private $collectionPrototype;

    /**
     * @var string
     */
    private $entityTable;

    /**
     * @var string
     */
    private $entityId = 'id';

    /**
     * @var Sql
     */
    private $sql;

    /**
     * @var Sql
     */
    private $slaveSql;

    /**
     * @var HydratingResultSet
     */
    private $resultSetPrototype;

    /**
     * @param Adapter $adapter
     * @param EntityInterface $entityPrototype
     * @param Adapter $slaveAdapter
     * @param mixed $collectionPrototype
     * @param HydratorInterface $hydrator
     */
    public function __construct(
        Adapter $adapter,
        EntityInterface $entityPrototype,
        Adapter $slaveAdapter       = null,
        $collectionPrototype        = null,
        HydratorInterface $hydrator = null
    ) {
        if (!$hydrator instanceof HydratorInterface) {
            $hydrator = new ClassMethods();
        }

        if (!is_object($entityPrototype)) {
            throw new Exception\InvalidArgumentException(
                'No entity prototype set.'
            );
        }

        if ($collectionPrototype instanceof CollectionInterface) {
            $this->collectionPrototype = $collectionPrototype;
        } elseif ($collectionPrototype instanceof HydratorInterface) {
            $hydrator = $collectionPrototype;
        } else {
            $this->collectionPrototype = new Collection();
        }

        $this->adapter         = $adapter;
        $this->slaveAdapter    = $slaveAdapter;
        $this->hydrator        = $hydrator;
        $this->entityPrototype = $entityPrototype;
    }

    /**
     * @param string
     * @return AbstractMapper
     */
    public function setEntityTable($entityTable)
    {
        $this->entityTable = $entityTable;

        return $this;
    }

    /**
     * @param int $id
     * @return object
     */
    public function findById($id)
    {
        $select = $this->getSelect()->where(array(
            $this->entityId => $id
        ));

        $entity = $this->select($select)->current();

        if (!$entity) {
            $entity = new $this->entityPrototype;
        }

        return $entity;
    }

    /**
     * @param array $conditions
     * @return object
     */
    public function findAll(array $conditions = array())
    {
        if (!$this->collectionPrototype instanceof CollectionInterface) {
            throw new Exception\InvalidArgumentException(
                'No collection prototype set.'
            );
        }

        /** @var CollectionInterface $collection */
        $collection = new $this->collectionPrototype;

        $select = $this->getSelect();

        if (!empty($conditions)) {
            $select->where($conditions);
        }

        $data = $this->select($select);

        foreach ($data as $entity) {
            if ($entity instanceof EntityInterface) {
                $collection->add($entity);
            }
        }

        return $collection;
    }

    public function save(EntityInterface $entity)
    {
        if (!$entity instanceof EntityInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Entity must be an instance of %s',
                EntityInterface::class
            ));
        }

        if (!$entity->isValid()) {
            $data = $this->create($entity);
            print_r($data);exit;
        }
    }

    public function saveAll(CollectionInterface $collection)
    {

    }

    /**
     * @param string $sql
     * @return StatementInterface|ResultSet
     */
    protected function query($sql)
    {
        return $this->adapter->query($sql);
    }

    /**
     * @return int
     */
    protected function getLastInsertId()
    {
        return $this->adapter->getDriver()->getLastGeneratedValue();
    }

    /**
     * @param string | null $table
     * @return Select
     */
    protected function getSelect($table = null)
    {
        return $this->getSlaveSql()->select(
            $table ?: $this->entityTable
        );
    }

    /**
     * @param Select $select
     * @param object | null $entityPrototype
     * @param HydratorInterface | null $hydrator
     * @return HydratingResultSet
     */
    protected function select(
        Select $select,
        $entityPrototype = null,
        HydratorInterface $hydrator = null
    ) {
        $statement = $this->getSlaveSql()->prepareStatementForSqlObject($select);

        $resultSet = new HydratingResultSet(
            $hydrator        ?: $this->hydrator,
            $entityPrototype ?: $this->getEntityPrototype()
        );

        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    /**
     * @param object | array $entity
     * @param string | TableIdentifier|null $entityTable
     * @param HydratorInterface | null $hydrator
     * @return ResultInterface
     */
    protected function create(
        $entity,
        $entityTable = null,
        HydratorInterface $hydrator = null
    ) {
        $tableName = $entityTable ?: $this->entityTable;
        $sql       = $this->getSql()->setTable($tableName);
        $insert    = $sql->insert();
        $rowData   = $this->entityToArray($entity, $hydrator);

        $insert->values($rowData);

        $statement = $sql->prepareStatementForSqlObject($insert);

        return $statement->execute();
    }

    /**
     * @param object | array $entity
     * @param string | array| \Closure $where
     * @param string | TableIdentifier| null $entityTable
     * @param HydratorInterface| null $hydrator
     * @return ResultInterface
     */
    protected function update(
        $entity,
        $where,
        $entityTable = null,
        HydratorInterface $hydrator = null
    ) {
        $tableName = $entityTable ?: $this->entityTable;
        $sql       = $this->getSql()->setTable($tableName);
        $update    = $sql->update();
        $rowData   = $this->entityToArray($entity, $hydrator);

        $update->set($rowData)->where($where);

        $statement = $sql->prepareStatementForSqlObject($update);

        return $statement->execute();
    }

    /**
     * @param string |array | \Closure $where
     * @param string |TableIdentifier | null $entityTable
     * @return ResultInterface
     */
    protected function delete($where, $entityTable = null)
    {
        $tableName = $entityTable ?: $this->entityTable;
        $sql       = $this->getSql()->setTable($tableName);
        $delete    = $sql->delete();

        $delete->where($where);

        $statement = $sql->prepareStatementForSqlObject($delete);

        return $statement->execute();
    }

    /**
     * @return string
     */
    protected function getEntityTable()
    {
        return $this->entityTable;
    }

    /**
     * @return object
     */
    protected function getEntityPrototype()
    {
        return $this->entityPrototype;
    }

    /**
     * @param object $entityPrototype
     * @return AbstractMapper
     */
    protected function setEntityPrototype($entityPrototype)
    {
        $this->entityPrototype    = $entityPrototype;
        $this->resultSetPrototype = null;
        return $this;
    }

    /**
     * @return Sql
     */
    protected function getSql()
    {
        if (!$this->sql instanceof Sql) {
            $this->sql = new Sql($this->adapter);
        }

        return $this->sql;
    }
    /**
     * @param Sql $sql
     * @return AbstractMapper
     */
    protected function setSql(Sql $sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * @return Sql
     */
    protected function getSlaveSql()
    {
        if (!$this->slaveSql instanceof Sql) {
            $this->slaveSql = new Sql($this->getSlaveAdapter());
        }

        return $this->slaveSql;
    }

    /**
     * @param Sql $sql
     * @return AbstractMapper
     */
    protected function setSlaveSql(Sql $sql)
    {
        $this->slaveSql = $sql;

        return $this;
    }

    /**
     * Uses the hydrator to convert the entity to an array.
     *
     * Use this method to ensure that you're working with an array.
     *
     * @param object $entity
     * @param HydratorInterface|null $hydrator
     * @return array
     */
    protected function entityToArray($entity, HydratorInterface $hydrator = null)
    {
        if (is_array($entity)) {
            return $entity;
        }

        if (is_object($entity)) {
            if (!$hydrator) {
                $hydrator = $this->hydrator;
            }

            return $hydrator->extract($entity);
        }

        throw new Exception\InvalidArgumentException(
            'Entity passed to db mapper should be an array or object.'
        );
    }

    /**
     * @return Adapter
     */
    private function getSlaveAdapter()
    {
        return $this->slaveAdapter ?: $this->adapter;
    }
}
