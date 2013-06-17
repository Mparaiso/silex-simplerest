<?php


namespace Service;

use DataAccessLayer\ISnippetProvider;

class SnippetService implements \Mparaiso\SimpleRest\IService
{
    /**
     * @var \DataAccessLayer\ISnippetProvider
     */
    protected $provider;

    function __construct(ISnippetProvider $provider)
    {
        $this->provider = $provider;
    }

    function findAll()
    {
        // TODO: Implement findAll() method.
    }

    function findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
    {
        // TODO: Implement findBy() method.
    }

    function findOneBy(array $criteria, array $orderBy = NULL)
    {
        // TODO: Implement findOneBy() method.
    }

    function persist($entity)
    {
        // TODO: Implement persist() method.
    }

    function remove($entity)
    {
        // TODO: Implement remove() method.
    }
}