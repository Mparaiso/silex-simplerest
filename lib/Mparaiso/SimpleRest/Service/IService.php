<?php

namespace Mparaiso\SimpleRest\Service;


use Mparaiso\SimpleRest\Model\AbstractModel;
use Mparaiso\SimpleRest\Model\IModel;

interface IService
{
    function find($id);

    function findBy(array $where, array $order, $limit, $offset);

    function findOneBy(array $where,array $order);

    function remove(IModel $model);

    function create(IModel $model);

    function update(IModel $model, array $where);

    function count(array $where);
}