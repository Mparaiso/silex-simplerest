<?php

namespace DataAccessLayer;


use Doctrine\DBAL\Connection;
use Model\IModel;

class DBALSnippetServiceProvider
{

    protected $connection;
    protected $table = "snippet";
    protected $model = '\Model\Snippet';
    protected $id = "id";

    function __construct(Connection $connexion)
    {
        $this->connection = $connexion;
    }

    function findAll()
    {
        $result = array();
        $rows = $this->connection->fetchAll("SELECT * FROM $this->table ;");
        foreach ($rows as $row) {
            array_push($result, new $this->model($row));
        }
        return $result;
    }

    function findById($id)
    {
        return $this->connection
            ->fetchAssoc("SELECT * FROM $this->table WHERE $this->id= :$this->id ;", array(
                "$this->id" => $id
            ));
    }

    function insert(IModel $model)
    {
        $data = $model->toArray();
        return $this->connection->insert($this->table, $data);
    }

    function update(IModel $model)
    {
        $data = $model->toArray();
        return $this->connection->update($this->table, $data, array("$this->id" => $data[$this->id]));
    }

    function delete(IModel $model)
    {
        $data = $model->toArray();
        return $this->connection->delete($this->table, array("$this->id" => $data[$this->id]));
    }


}
