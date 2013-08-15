<?php


namespace Mparaiso\SimpleRest\Service;

use Mparaiso\SimpleRest\Model\IModel;
use Mparaiso\SimpleRest\Provider\IProvider;
use Mparaiso\SimpleRest\Service\IService;

class Service implements IService
{
    /**
     * @var \Mparaiso\SimpleRest\Provider\IProvider
     */
    protected $provider;

    function __construct(IProvider $provider)
    {
        $this->provider = $provider;
    }

    function find($id)
    {
        return $this->provider->find($id);
    }

    function findBy(array $where = array(), array $order = array(), $limit = NULL, $offset = NULL)
    {
        return $this->provider->findBy($where, $order, $limit, $offset);
    }

    function findOneBy(array $where = array(), array $order = array())
    {
        $result = $this->findBy($where, $order, 1);
        if (is_array($result)) {
            return $result[0];
        }
    }

    function remove(IModel $model)
    {
        return $this->provider->remove($model);
    }

    function create(IModel $model)
    {
        return $this->provider->create($model);
    }

    function update(IModel $model, array $where)
    {
        return $this->provider->update($model, $where);
    }

    function count(array $where = array())
    {
        return $this->provider->count($where);
    }
}