<?php
/**
 * @author Mparaiso <mparaiso@online.fr>
 */
namespace DataAccessLayer;

/**
 * Class PDODataProvider
 * @package DataAccessLayer
 */
abstract class PDODataProvider implements IDataProvider
{
    /**
     * @var string model class name
     */
    protected $model;
    /**
     * @var string table name
     */
    protected $table;
    /**
     * @var \PDO PDO connection
     */
    protected $connection;
    /**
     * @var array
     */
    protected $fields;
    /**
     * @var string
     */
    protected $id;

    function __construct(\PDO $connection, array $properties = array())
    {
        foreach ($properties as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

    }


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param \PDO $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }
}