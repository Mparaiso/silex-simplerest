<?php
/**
 * @author Mparaiso <mparaiso@online.fr>
 */
namespace Mparaiso\SimpleRest\Provider;

use Doctrine\DBAL\Connection;
use Mparaiso\SimpleRest\Model\AbstractModel;
use Mparaiso\SimpleRest\Provider\IProvider;

class DBALProvider implements IProvider
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var object
     */
    protected $model;
    /**
     * @var string
     */
    protected $id;

    function __construct(Connection $connection, array $params = array())
    {
        $this->connection = $connection;
        foreach ($params as $key => $value) {
            if (property_exists($this, $key) && $value != NULL) {
                $this->$key = $value;
            }
        }
    }

    function findAll()
    {
        return $this->findBy();
    }

    function findBy(array $criteria = array(), array $order = array(), $limit = NULL, $offset = NULL)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select("*")->from($this->name, "t");
        foreach ($criteria as $key => $value) {
            $qb->andWhere("$key = :$key");
            $qb->setParameter(":$key", $value);
        }
        foreach ($order as $key => $value) {
            $qb->addOrderBy($key, $value);
        }
        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);
        $stmt = $qb->execute();
        /* @var \PDOStatement $stmt */
        $results = array();
        while ($model = $stmt->fetchObject($this->model)) {
            array_push($results, $model);
        }
        return $results;
    }

    function find($id)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select("*")->from($this->name, "t")->where("t.id = :id");
        $qb->setMaxResults(1);
        $qb->setParameter(":id", $id);
        $stmt = $qb->execute();
        /* @var \PDOStatement $stmt */
        return $stmt->fetchObject($this->model);

    }

    function remove(AbstractModel $model)
    {
        $data = $model->toArray();
        return $this->connection->delete($this->name, array("$this->id" => $data[$this->id]));
    }

    function create(AbstractModel $model)
    {
        $data = $model->toArray();
        $affectedRows = $this->connection->insert($this->name, $data);
        if ($affectedRows) {
            return $this->connection->lastInsertId();
        }
    }

    function update(AbstractModel $model, array $where)
    {
        $data = $model->toArray();
        return $this->connection->
            update($this->name, $data, $where);
    }

    function count(array $where = array())
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select(" count(*) as count ")->from($this->name, "t");
        foreach ($where as $key => $value) {
            if ($value != NULL) {
                $qb->andWhere("$key = :$key");
                $qb->setParameter(":$key", $value);
            }
        }
        $stmt = $qb->execute();
        /* @var \PDOStatement $stmt */
        return $stmt->fetchColumn();
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }


}