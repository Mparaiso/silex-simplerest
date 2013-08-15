<?php

namespace Mparaiso\SimpleRest\Model;

interface IModel extends \JsonSerializable
{
    function toArray();
}