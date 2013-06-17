<?php

namespace Mparaiso\SimpleRest;

use Silex\Application;
use Silex\ControllerProviderInterface;

class Controller implements ControllerProviderInterface
{
    protected $resourceName;
    protected $serviceName;
    protected $modelClass;

    function __construct(array $parameters = array())
    {
        foreach ($parameters as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    function index(Application $app)
    {
    }

    function get(Application $app)
    {
    }

    function put(Application $app)
    {
    }

    function post(Application $app)
    {
    }

    function delete(Application $app,$id)
    {
    }


    public function connect(Application $app)
    {
        $controllers = $app["controllers_factory"];
        /* @var \Silex\ControllerCollection $controllers */
        $controllers->get("/{$this->resourceName}", array($this, "index"));
        $controllers->get("/{$this->resourceName}/{id}", array($this, "get"));
        $controllers->post("/{$this->resourceName}",array($this,"post"));
        $controllers->put("/{$this->resourceName}",array($this,"put"));
        $controllers->delete("/{$this->resourceName}",array($this,"delete"));
        $controllers->delete("/{$this->resourceName}/{id}",array($this,"delete"));
        return $controllers;
    }
}
