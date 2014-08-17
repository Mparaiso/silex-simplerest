<?php

/**
 * Class Table
 * Simple file database , data is saved as JSON
 */
class Table
{
    private $fileName;

    function __construct($fileName)
    {
        if (!file_exists($fileName)) {
            touch($fileName);
        }
        $this->fileName = $fileName;
    }

    function insert(&$model)
    {
        $collection = $this->readCollection();
        $model->id = uniqid();
        $collection[] = $model;
        $this->writeCollection($collection);
        return $model;
    }

    function delete(&$model)
    {
        $newCollection = array_filter($this->readCollection(), function ($m) use ($model) {
            return $m['id'] !== $model->id;
        });
        $this->writeCollection($newCollection);
        unset($model->id);
        return $model;
    }

    function update(&$model)
    {
        $collection = $this->readCollection();
        for ($i = 0; $i < count($collection); $i++) {
            if ($collection[$i]['id'] == $model->id) {
                $collection[$i] = $model;
                $this->writeCollection($collection);
                return $model;
            }
        }
        return null;
    }

    function read($id)
    {
        $collection = $this->readCollection();
        for ($i = 0; $i < count($collection); $i++) {
            if ($collection[$i]->id == $id) {
                return $collection[$i];
            }
        }
        return null;
    }

    function index()
    {
        return $this->readCollection();
    }

    private function readCollection()
    {
        $collection = unserialize(file_get_contents($this->fileName));
        if (NULL == $collection) {
            $collection = array();
        }
        return $collection;
    }

    private function writeCollection(array $collection)
    {
        return file_put_contents($this->fileName, serialize($collection));
    }


}
