<?php

namespace Mock;

use Mparaiso\SimpleRest\Service\RestServiceInterface;

class RestServiceMock implements RestServiceInterface
{
    /**
     * @var ModelMock[]
     */
    private $resources = array();

    function findResource($id)
    {
    }

    function findResourceBy($where)
    {

    }

    function findAllResource($where)
    {
        // TODO: Implement findAllResource() method.
    }

    function findOneResourceBy($where)
    {
        // TODO: Implement findOneResourceBy() method.
    }

    function deleteResource($model)
    {
        // TODO: Implement deleteResource() method.
    }

    function createResource($model)
    {
        // TODO: Implement createResource() method.
    }

    function updateResource($model)
    {
        // TODO: Implement updateResource() method.
    }
}