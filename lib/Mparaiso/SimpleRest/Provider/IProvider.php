<?php

namespace Mparaiso\SimpleRest\Provider;


use Mparaiso\SimpleRest\Model\AbstractModel;

interface IProvider
{
    function find($id);

    function findBy(array $where, array $order, $limit, $offset);

    function remove(AbstractModel $model);

    function create(AbstractModel $model);

    function update(AbstractModel $model, array $where);

    function count(array $where);

    function getConnection();

    function getName();

    function getModel();

    function getId();
}