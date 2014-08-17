<?php

use Mparaiso\SimpleRest\Controller\Controller;
use Silex\Application;
use Silex\Provider\SerializerServiceProvider;

$autoload = require_once __DIR__ . '/../vendor/autoload.php';
$autoload->add("", __DIR__);

class BootStrap
{
    static function getApplication($boot = true)
    {
        $app = new Silex\Application();
        $app['debug'] = true;
        $app['exception_handler']->disable();
        $app['model'] = '\Mock\ModelMock';
        $app['rest_service'] = $app->share(function () {
            return new \Mock\RestServiceMock();
        });
        $app['rest_controller'] = $app->share(function ($app) {
            return new Controller(array(
                'service' => $app['rest_service'],
                'model' => $app['model'],
                'resource' => 'resource'
            ));
        });
        $app->register(new SerializerServiceProvider());
        $boot == TRUE AND $app->boot();

        return $app;

    }
}