<?php

use Mparaiso\Provider\ConsoleServiceProvider;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Symfony\Component\HttpFoundation\Request;

class Config implements \Silex\ServiceProviderInterface
{


    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app->register(new DoctrineServiceProvider(), array(
            "db.options" => array(
                "driver"   => "pdo_mysql",
                "host"     => "localhost",
                "dbname"   => getenv("SIMPE_REST_DB"),
                "user"     => getenv("SIMPLE_REST_USER"),
                "password" => getenv("SIMPLE_REST_PASSWORD")
            )
        ));
        $app->register(new ConsoleServiceProvider());
        $app->get("/", function (Request $req, Application $app) {
            $name = $req->query->get("name", "World");
            return "Hello " . $name . "!";
        });

        $app["console"] = $app->share($app->extend("console", function ($console, $app) {
            $console->add(new \Command\GenerateDatabaseCommand);
            return $console;
        }));
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}
