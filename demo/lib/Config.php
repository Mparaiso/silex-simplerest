<?php

use Mparaiso\Provider\ConsoleServiceProvider;
use Mparaiso\SimpleRest\Controller\Controller;
use Silex\Application;
use Silex\Provider\SerializerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Serializer;

class Config implements ServiceProviderInterface
{

    /**
     * @inheritdoc
     */
    public function register(Application $app)
    {
        $app->register(new SerializerServiceProvider());
        $app->register(new UrlGeneratorServiceProvider());

        $app['data_folder'] = __DIR__ . '/../data';
        $app['notes'] = $app->share(function ($app) {
            return new Table($app['data_folder'] . '/notes.txt');
        });
        $app['categories'] = $app->share(function ($app) {
            return new Table($app['data_folder'] . '/categories.txt');
        });
        $app['rest_notes'] = $app->share(function ($app) {
            return new RestService($app['notes']);
        });
        $app['rest_categories'] = $app->share(function ($app) {
            return new RestService($app['categories']);
        });


        $app['controller_note'] = $app->share(function ($app) {
            return new Controller(array(
                'resource' => 'note',
                'model' => '\Model\Note',
                'service' => $app['rest_notes']
            ));
        });

        $app['controller_category_note'] = $app->share(function ($app) {
            return new Controller(array(
                'resource' => 'note',
                'model' => '\Model\Note',
                'service' => $app['rest_notes']
            ));
        });

        $app['controller_category'] = $app->share(function ($app) {
            return new Controller(array(
                'resource' => 'category',
                'model' => '\Model\Category',
                'service' => $app['rest_categories'],
                'children' => array($app['controller_category_note'])
            ));
        });

        # EVENT HANDLERS

        $app['add_parent_id_to_query'] = $app->protect(function (GenericEvent $event) {
            /* @var Request $request */
            $controller = $event->getArgument('controller');
            if (NULL !== $controller->parent) {
                $parent = $event->getArgument('parent');
                $query = $event->getArgument('query');
                $parent_id_property = "{$controller->parent->resource}_id";
                $query[$parent_id_property] = $parent->id;
            }
        });

        $app['add_parent_id_to_model'] = $app->protect(function (GenericEvent $event) {
            /* @var Request $request */
            $controller = $event->getArgument('controller');
            if (NULL !== $controller->parent) {
                $parent = $event->getArgument('parent');
                $model = $event->getArgument('model');
                $parent_id_property = "{$controller->parent->resource}_id";
                $model->{$parent_id_property} = $parent->id;
            }
        });

        $app['resource_belongs_to_parent_or_throw'] = $app->protect(function (GenericEvent $event) {
            $controller = $event->getArgument('controller');
            if (NULL !== $controller->parent) {
                $parent = $event->getArgument('parent');
                $model = $event->getSubject();
                $parent_id_property = "{$controller->parent->resource}_id";
                if ($model->{$parent_id_property} != $parent->id) {
                    throw new NotFoundHttpException("resource with id {$model->id} doesnt belongs to parent resource with id {$parent->id}");
                }
            }
        });
        $app['rest_error_handler'] = $app->protect(function (\Exception $exception, $code) use ($app) {
            $res = null;
            /* @var Request $req */
            $req = $app['request'];
            /* @var Serializer $serializer */
            $serializer = $app['serializer'];
            $contentType = $req->getContentType();
            if (in_array($contentType, array('json', 'xml'))) {
                $res = new Response($serializer->serialize(
                        array('stack' => $exception->getTraceAsString(), 'message' => $exception->getMessage()), $contentType),
                    $code);
            }
            return $res;
        });
    }

    /**
     * @inheritdoc
     */
    public function boot(Application $app)
    {
        $app->get('/.{_format}', function (Application $app, $_format) {
            return $app['serializer']->serialize(array('status' => 200, 'message' => 'ok',), $_format);
        })->bind('index')->value('_format', 'json');
        $app->mount('/', $app['controller_note']);
        $app->mount('/', $app['controller_category']);

        #handle note relationships
        $app->on('note_before_index', $app['add_parent_id_to_query']);
        $app->on('note_before_create', $app['add_parent_id_to_model']);
        $app->on('note_before_delete', $app['resource_belongs_to_parent_or_throw']);
        $app->on('note_before_read', $app['resource_belongs_to_parent_or_throw']);
        $app->on('note_before_update', $app['resource_belongs_to_parent_or_throw']);

        $app->error($app['rest_error_handler']);
    }

}