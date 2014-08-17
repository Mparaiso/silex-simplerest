<?php
/**
 * @author mparaiso <mparaiso@online.fr>
 */
namespace Mparaiso\SimpleRest\Controller;

use ArrayObject;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\Route;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Serializer;

/**
 * Class Controller
 * FR : Controleur générique pour une interface de type REST.
 * @package Mparaiso\SimpleRest\Controller
 * @property-read $namespace
 * @property-read $indexVerb
 * @property-read $readVerb
 * @property-read $createVerb
 * @property-read $updateVerb
 * @property-read $deleteVerb
 * @property-read $allow
 * @property-read $defaultFormat
 * @property-read $formats
 * @property-read $model
 * @property-read $resource
 * @property-read $resourcePluralize
 * @property-read \Mparaiso\SimpleRest\Service\RestServiceInterface $service
 * @property-read $beforeDelete
 * @property-read $afterDelete
 * @property-read $beforeRead
 * @property-read $afterRead
 * @property-read $beforeCreate
 * @property-read $afterCreate
 * @property-read $beforeUpdate
 * @property-read $afterUpdate
 * @property-read $beforeIndex
 * @property-read $afterIndex
 * @property-read $createRoute
 * @property-read $indexRoute
 * @property-read $readRoute
 * @property-read $updateRoute
 * @property-read $deleteRoute
 * @property-read $prefix
 */
class Controller implements ControllerProviderInterface
{
    /**
     * properties
     */
    private $namespace = "mp_simplerest_";
    private $indexVerb = 'get';
    private $readVerb = 'get';
    private $createVerb = 'post';
    private $updateVerb = 'put';
    private $deleteVerb = 'delete';
    private $allow = array('create', 'update', 'read', 'index', 'delete', 'count');
    private $defaultFormat = 'json';
    private $formats = array('json', 'xml');
    private $model;
    private $resource;
    private $resourcePluralize;
    private $service;
    private $beforeDelete;
    private $afterDelete;
    private $beforeRead;
    private $afterRead;
    private $beforeCreate;
    private $afterCreate;
    private $beforeUpdate;
    private $afterUpdate;
    private $beforeIndex;
    private $afterIndex;
    protected $createRoute;
    protected $indexRoute;
    protected $readRoute;
    protected $updateRoute;
    protected $deleteRoute;
    protected $prefix = "";
    /**
     * @var Controller[]
     */
    private $children = array();
    /**
     * @var Controller parent rest controller
     */
    protected $parent;
    /**
     * @var callable
     */
    protected $customRoutesProvider;

    /**
     * show detail messages
     * @var string
     */
    protected $debug;
    protected $defaultErrorMessage = "Error";

    /**
     * FR : constructeur<br>
     * EN : constructor<br>
     * @param array $parameters
     */
    function __construct(array $parameters = array())
    {
        foreach ($parameters as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        if (NULL == $this->resource || !is_string($this->resource)) {
            throw new Exception('Controller::resource must be a string');
        }
        if (!$this->service instanceof \Mparaiso\SimpleRest\Service\RestServiceInterface) {
            throw new Exception('service parameter must be an instance of \Mparaiso\SimpleRest\Service\RestServiceInterface');
        }
        if ($this->beforeCreate == NULL) {
            $this->beforeCreate = $this->resource . "_before_create";
        }
        if ($this->afterCreate == NULL) {
            $this->afterCreate = $this->resource . "_after_create";
        }
        if ($this->beforeRead == NULL) {
            $this->beforeRead = $this->resource . "_before_read";
        }
        if ($this->afterRead == NULL) {
            $this->afterRead = $this->resource . "_after_read";
        }
        if ($this->beforeDelete == NULL) {
            $this->beforeDelete = $this->resource . "_before_delete";
        }
        if ($this->afterDelete == NULL) {
            $this->afterDelete = $this->resource . "_after_delete";
        }
        if ($this->beforeUpdate == NULL) {
            $this->beforeUpdate = $this->resource . "_before_update";
        }
        if ($this->afterUpdate == NULL) {
            $this->afterUpdate = $this->resource . "_after_update";
        }
        if ($this->beforeIndex == NULL) {
            $this->beforeIndex = $this->resource . "_before_index";
        }
        if ($this->afterIndex == NULL) {
            $this->afterIndex = $this->resource . "_after_index";
        }
        if ($this->resource && $this->resourcePluralize == NULL) {
            $this->resourcePluralize = $this->resource . "s";
        }
        // create route names if not specified in parameters
        if (NULL == $this->createRoute)
            $this->createRoute = "$this->namespace{$this->resource}_create";
        if (NULL == $this->readRoute)
            $this->readRoute = "$this->namespace{$this->resource}_read";
        if (NULL == $this->deleteRoute)
            $this->deleteRoute = "$this->namespace{$this->resource}_delete";
        if (NULL == $this->updateRoute)
            $this->updateRoute = "$this->namespace{$this->resource}_update";
        if (NULL == $this->indexRoute)
            $this->indexRoute = "$this->namespace{$this->resource}_index";
        if (!is_callable(array($this, 'customRoutesProvider'))) {
            $this->customRoutesProvider = function ($controller) {
            };
        }
        foreach ($this->children as $child) {
            $child->parent = $this;
        }
    }

    /**
     * @param $name
     * @return null|mixed
     */
    function __get($name)
    {
        $value = null;
        if (property_exists($this, $name)) {
            $value = $this->$name;
        }
        return $value;
    }

    /**
     * List resources
     * @param Request $req
     * @param Application $app
     * @param $_format
     * @return Response
     */
    function index(Request $req, Application $app, $_format)
    {
        /* @var EventDispatcher $dispatcher */
        $dispatcher = $app['dispatcher'];
        /* @var Serializer $serializer */
        $serializer = $app['serializer'];
        $query = new ArrayObject($req->query->all());
        $parent = $this->getParentResource($req->attributes->get('parent_id'));
        $dispatcher->dispatch($this->beforeIndex, new GenericEvent($query, array('query' => $query, 'parent' => $parent, 'controller' => $this, 'request' => $req, 'app' => $app)));
        $collection = $this->service->findResourceBy($query);
        $response = new Response($serializer->serialize($collection, $_format), Response::HTTP_OK);
        $dispatcher->dispatch($this->afterIndex, new GenericEvent($collection, array('request' => $req, 'response' => $response, 'collection' => $collection, 'controller' => $this, 'app' => $app)));
        return $response;
    }

    /**
     * @param Request $req
     * @param Application $app
     * @param $id
     * @param $_format
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    function read(Request $req, Application $app, $id, $_format)
    {
        /* @var EventDispatcher $dispatcher */
        $dispatcher = $app['dispatcher'];
        /* @var Serializer $serializer */
        $serializer = $app['serializer'];
        $query = new ArrayObject($req->query->all());
        $parent = $this->getParentResource($req->attributes->get('parent_id'));
        $model = $this->service->findResource($id);
        if (NULL == $model) throw new NotFoundHttpException("resource $this->resource with id $id not found");
        $dispatcher->dispatch($this->beforeRead, new GenericEvent($model, array('parent' => $parent, 'request' => $req, 'controller' => $this, 'app' => $app)));
        $response = new Response($serializer->serialize($model, $_format), Response::HTTP_OK);
        $dispatcher->dispatch($this->afterRead, new GenericEvent($model, array('controller' => $this, 'request' => $req, 'response' => $response, 'model' => $model, 'app' => $app)));
        return $response;
    }

    /**
     * create a resource
     * @param Request $req
     * @param Application $app
     * @param $_format
     * @return Response
     */
    function create(Request $req, Application $app, $_format)
    {
        /* @var EventDispatcher $dispatcher */
        $dispatcher = $app['dispatcher'];
        /* @var Serializer $serializer */
        $serializer = $app['serializer'];
        $model = $serializer->deserialize($req->getContent(), $this->model, $_format);
        $dispatcher->dispatch($this->beforeCreate, new GenericEvent($model, array('controller' => $this, 'request' => $req)));
        $model = $this->service->createResource($model);
        $response = new Response($serializer->serialize($model, $_format), Response::HTTP_CREATED);
        $dispatcher->dispatch($this->afterCreate, new GenericEvent($model, array('controller' => $this, 'request' => $req, 'response' => $response)));
        return $response;
    }

    /**
     * @param Request $req
     * @param Application $app
     * @param $id
     * @param $_format
     * @return Response
     * @throws NotFoundHttpException
     */
    function update(Request $req, Application $app, $id, $_format)
    {
        /* @var EventDispatcher $dispatcher */
        $dispatcher = $app['dispatcher'];
        /* @var Serializer $serializer */
        $serializer = $app['serializer'];
        $parent = $this->getParentResource($req->attributes->get('parent_id'));
        $exists = $this->service->findResource($id);
        if (NULL == $exists) throw new NotFoundHttpException("resource $this->resource with id $id not found");
        $model = $serializer->deserialize($req->getContent(), $this->model, $_format);
        $dispatcher->dispatch($this->beforeUpdate, new GenericEvent($model, array('app' => $app, 'parent' => $parent, 'id' => $id, 'request' => $req)));
        $model = $this->service->updateResource($model);
        $response = new Response($serializer->serialize($model, $_format), Response::HTTP_OK);
        $dispatcher->dispatch($this->afterUpdate, new GenericEvent($model, array('response' => $response, 'id' => $id, 'request' => $req)));
        return $response;
    }

    /**
     * @param Request $req
     * @param Application $app
     * @param $id
     * @param $_format
     * @return Response
     * @throws NotFoundHttpException
     */
    function delete(Request $req, Application $app, $id, $_format)
    {
        /* @var EventDispatcher $dispatcher */
        $dispatcher = $app['dispatcher'];
        /* @var Serializer $serializer */
        $serializer = $app['serializer'];
        $parent = $this->getParentResource($req->attributes->get('parent_id'));
        $model = $this->service->findResource($id);
        if (NULL == $model) throw new NotFoundHttpException("resource $this->resource with id $id not found");
        $dispatcher->dispatch($this->beforeDelete, new GenericEvent($model, array('parent' => $parent, 'request' => $req, 'controller' => $this, 'app' => $app)));
        $result = $this->service->deleteResource($model);
        $response = new Response($serializer->serialize($result, $_format), Response::HTTP_ACCEPTED);
        $dispatcher->dispatch($this->afterDelete, new GenericEvent($model, array('request' => $req, 'model' => $model, 'result' => $result, 'response' => $response, 'controller' => $this, 'app' => $app)));
        return $response;
    }


    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app["controllers_factory"];
        /* @var ControllerCollection|Route $controllers */
        $this->addCustomRoutes($controllers);
        # custom hooks
        call_user_func_array($this->customRoutesProvider, array($controllers));
        if (in_array("create", $this->allow))
            $controllers->match("$this->prefix/$this->resource.{_format}", array($this, "create"))
                ->bind($this->createRoute)
                ->method($this->createVerb);
        if (in_array("update", $this->allow))
            $controllers->match("$this->prefix/$this->resource/{id}.{_format}", array($this, "update"))
                ->bind($this->updateRoute)
                ->method($this->updateVerb);
        if (in_array("delete", $this->allow))
            $controllers->match("$this->prefix/$this->resource/{id}.{_format}", array($this, "delete"))
                ->bind($this->deleteRoute)
                ->method($this->deleteVerb);
        if (in_array("index", $this->allow))
            $controllers->match("$this->prefix/$this->resource.{_format}", array($this, "index"))
                ->bind($this->indexRoute)
                ->method($this->indexVerb);
        if (in_array("read", $this->allow))
            $controllers->match("$this->prefix/$this->resource/{id}.{_format}", array($this, "read"))
                ->bind($this->readRoute)
                ->method($this->readVerb);
        $controllers->value("_format", $this->defaultFormat)->assert("_format", implode("|", $this->formats));
        /* mount child resource controllers */
        foreach ($this->children as $child) {
            $child = clone $child;
            $child->prefix = "/$this->prefix/$this->resource/{parent_id}";
            $child->createRoute = "$this->namespace{$this->resource}_{$child->createRoute}";
            $child->indexRoute = "$this->namespace{$this->resource}_{$child->indexRoute}";
            $child->readRoute = "$this->namespace{$this->resource}_{$child->readRoute}";
            $child->updateRoute = "$this->namespace{$this->resource}_{$child->updateRoute}";
            $child->deleteRoute = "$this->namespace{$this->resource}_{$child->deleteRoute}";
            $child->countRoute = "$this->namespace{$this->resource}_{$child->countRoute}";
            $child = $child->connect($app);
            $controllers->mount("/", $child);
        }
        return $controllers;
    }

    /**
     * @param Controller $controller
     * @return $this
     */
    function addChild(Controller $controller)
    {
        $this->children[] = $controller;
        $controller->parent = $this;
        return $this;
    }

    /**
     * @param ControllerCollection $controllers
     */
    protected function addCustomRoutes(ControllerCollection $controllers)
    {
    }

    /**
     * @param string|int $parent_id
     * @return null|mixed
     */
    protected function getParentResource($parent_id)
    {
        $parent = NULL;
        if ($this->parent) {
            $parent = $this->parent->service->findResource($parent_id);
        }
        return $parent;
    }

}
