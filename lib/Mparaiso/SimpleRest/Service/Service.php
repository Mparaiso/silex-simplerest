<?php


namespace Mparaiso\SimpleRest\Service;

use Mparaiso\SimpleRest\Model\AbstractModel;
use Mparaiso\SimpleRest\Provider\IProvider;
use Mparaiso\SimpleRest\Service\IService;

class Service implements IService
{

    protected $provider;

    function __construct(IProvider $provider)
    {
        $this->provider = $provider;
    }

    function find($id)
    {
        return $this->provider->find($id);
    }

    function findBy(array $where = array(), array $order = array(), $limit, $offset)
    {
        return $this->provider->findBy($where, $order, $limit, $offset);
    }

    function remove(AbstractModel $model)
    {
        return $this->provider->remove($model);
    }

    function create(AbstractModel $model)
    {
        return $this->provider->create($model);
    }

    function update($model, array $where)
    {
        return $this->provider->update($model, $where);
    }

    function count(array $where = array())
    {
        return $this->provider->count($where);
    }
}