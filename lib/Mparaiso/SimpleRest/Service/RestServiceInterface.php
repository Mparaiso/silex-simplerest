<?php

namespace Mparaiso\SimpleRest\Service;


interface RestServiceInterface
{
    function findResource($id);

    function findResourceBy($where);

    function findAllResource($where);

    function findOneResourceBy($where);

    function deleteResource($model);

    function createResource($model);

    function updateResource($model);

}
