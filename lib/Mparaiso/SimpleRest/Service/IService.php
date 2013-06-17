<?php

namespace Mparaiso\SimpleRest\Service;


use Mparaiso\SimpleRest\Model\AbstractModel;

interface IService
{
    function find($id);

    function findBy(array $where, array $order, $limit, $offset);

    function remove(AbstractModel $model);

    function create(AbstractModel $model);

    function update( $model, array $where);

    function count(array $where);
}