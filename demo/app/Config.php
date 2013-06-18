<?php

use Command\AddCategoriesCommand;
use Command\AddDefaultSnippetsCommand;
use Command\GenerateDatabaseCommand;
use Doctrine\DBAL\Connection;
use Mparaiso\Provider\ConsoleServiceProvider;
use Mparaiso\SimpleRest\Controller\Controller;
use Mparaiso\SimpleRest\Provider\DBALProvider;
use Mparaiso\SimpleRest\Service\Service;
use Service\SnippetService;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SerializerServiceProvider;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class Config<br>
 * FR : Configuration de l'application<br>
 * EN : Application Configuration<br>
 */
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

        $app["db"] = $app->share($app->extend("db", function ($db, $app) {
            /*  @var Connection $db */
            return $db;
        }));

        $app->register(new SerializerServiceProvider());
        $app->register(new ConsoleServiceProvider());
        $app->register(new MonologServiceProvider(), array(
            "monolog.logfile" => __DIR__ . "/../temp/" . date("Y-m-d") . ".txt"));


        $app["console"] = $app->share($app->extend("console", function ($console, $app) {
            $console->add(new GenerateDatabaseCommand);
            $console->add(new AddCategoriesCommand);
            $console->add(new AddDefaultSnippetsCommand);
            return $console;
        }));

        $app['snippet_provider'] = $app->share(function ($app) {
            return new DBALProvider($app["db"], array(
                "model" => '\Model\Snippet',
                "name"  => "snippet",
                "id"    => "id"
            ));
        });

        $app["snippet_service"] = $app->share(function ($app) {
            return new SnippetService($app["snippet_provider"]);
        });

        $app["snippet_controller"] = $app->share(function ($app) {
            $controller = new Controller(array(
                "resource"          => "snippet",
                "resourcePluralize" => "snippets",
                "model"             => '\Model\Snippet',
                "service"           => $app["snippet_service"]
            ));
            return $controller;
        });

        $app['category_provider'] = $app->share(function ($app) {
            return new DBALProvider($app["db"], array(
                "model" => '\Model\Category',
                "name"  => "category",
                "id"    => "id"
            ));
        });

        $app["category_service"] = $app->share(function ($app) {
            return new Service($app["category_provider"]);
        });

        $app["category_controller"] = $app->share(function ($app) {
            $controller = new Controller(array(
                "resource"          => "category",
                "resourcePluralize" => "categories",
                "model"             => '\Model\Category',
                "service"           => $app["category_service"]
            ));
            return $controller;
        });


        $app["snippet_before_create"] = $app->protect(function (GenericEvent $event) {
            $model = $event->getSubject();
            $now = new DateTime;
            $date = $now->format("Y-m-d H:i:s");
            $model->setCreatedAt($date);
            $model->setUpdatedAt($date);
        });

        $app["snippet_before_update"] = $app->protect(function (GenericEvent $event) {
            $model = $event->getSubject();
            $now = new DateTime;
            $date = $now->format("Y-m-d H:i:s");
            $model->setUpdatedAt($date);
        });

    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app->get("/", function () {
            $content = file_get_contents( __DIR__ . '/../web/static/js/snippetd/partials/index.html');
            return $content;
        });
        $app->mount("/api/", $app["snippet_controller"]);
        $app->mount("/api/", $app["category_controller"]);

        $app->on("snippet_before_create", $app["snippet_before_create"]);
        $app->on("snippet_before_update", $app["snippet_before_update"]);


    }
}
