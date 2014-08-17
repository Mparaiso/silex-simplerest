<?php

use Mparaiso\SimpleRest\Service\RestServiceInterface;

class RestService implements RestServiceInterface
{
    private $db;

    function __construct(Table $db)
    {
        $this->db = $db;
    }

    function findResource($id)
    {
        return $this->db->read($id);
    }

    function findResourceBy($where)
    {
        $collection = $this->db->index();
        $collection = array_filter($collection, function ($item) use ($where) {
            $result = TRUE;
            foreach ($where as $key => $value) {
                if (!property_exists($item, $key) || $where[$key] !== $item->{$key}) {
                    $result = FALSE;
                    break;
                }
            }
            return $result;
        });
        return $collection;
    }

    function findAllResource($where)
    {
        return $this->db->index();
    }

    function findOneResourceBy($where)
    {
        $collection = $this->findResourceBy($where);
        $model = count($collection) > 0 ? $collection[0] : NULL;
        return $model;
    }

    function deleteResource($model)
    {
        return $this->db->delete($model);
    }

    function createResource($model)
    {
        return $this->db->insert($model);
    }

    function updateResource($model)
    {
        return $this->db->update($model);
    }

}