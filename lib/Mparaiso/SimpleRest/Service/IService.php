<?php

namespace Mparaiso\SimpleRest\Service;


use Mparaiso\SimpleRest\Model\AbstractModel;
use Mparaiso\SimpleRest\Model\IModel;

interface IService
{
    function find($id);

    function findBy(array $where, array $order, $limit, $offset);

    function findOneBy(array $where);

    function remove($model);

    function create($model);

    function update($model, array $where);

    function count(array $where);
}
