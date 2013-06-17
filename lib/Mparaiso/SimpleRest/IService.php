<?php

namespace Mparaiso\SimpleRest;

/**
 * Class IService
 * based on Doctrine EntityRepository
 * @author mparaiso <mparaiso@online.fr>
 *
 */
interface IService{
    function findAll();
    function findBy(array $criteria,array $orderBy=NULL,$limit=NULL,$offset=NULL);
    function findOneBy(array $criteria,array $orderBy=NULL);
    function persist($entity);
    function remove($entity);
}
