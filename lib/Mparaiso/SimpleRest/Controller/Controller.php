<?php
/**
 * @author mparaiso <mparaiso@online.fr>
 */
namespace Mparaiso\SimpleRest\Controller;


use Exception;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class Controller
 * FR : Controleur générique pour une interface de type REST.
 * @package Mparaiso\SimpleRest\Controller
 */
class Controller implements ControllerProviderInterface
{
    /**
     * properties
     */
    const SUCCESS = 200;
    const RESOURCE_CREATED = 201;
    const SUCCESS_NO_RETURN = 204;
    const VALIDATION_ERROR = 400;
    const NOT_AUTHENTICATED = 401;
    const WRONG_CREDENTIALS = 403;
    const NOT_FOUND = 404;
    const OTHER_ERROR = 500;

    public $findByMethod = "findBy";
    public $findAllMethod = "findAll";
    public $findMethod = "find";
    public $createMethod = "create";
    public $updateMethod = "update";
    public $deleteMethod = "remove";
    public $countMethod = "count";
    public $id = "id";
    public $indexVerb = "get";
    public $readVerb = "get";
    public $createVerb = "post";
    public $updateVerb = "post";
    public $deleteVerb = "delete";
    public $allow = array("create", "update", "read", "index", "delete", "count");
    public $defaultFormat = "json";
    public $formats = array("json", "xml");
    public $resource;
    public $resourcePluralize;
    public $service;
    public $model;
    public $criteria;
    public $beforeDelete;
    public $afterDelete;
    public $beforeRead;
    public $afterRead;
    public $beforeCreate;
    public $afterCreate;
    public $beforeUpdate;
    public $afterUpdate;
    public $beforeIndex;
    public $afterIndex;
    public $createRoute;
    public $indexRoute;
    public $readRoute;
    public $updateRoute;
    public $deleteRoute;
    public $countRoute;

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
        if ($this->criteria == NULL) {
            $this->criteria = array();
            $reflc = new \ReflectionClass($this->model);
            $props = $reflc->getProperties();
            foreach ($props as $prop) {
                array_push($this->criteria, $prop->getName());
            }
        }
        if ($this->resource && $this->resourcePluralize == NULL) {
            $this->resourcePluralize = $this->resource . "s";
        }
        // create route names if not specified in parameters
        if (NULL == $this->createRoute)
            $this->createRoute = "mp_simplerest_" . $this->resource . "_create";
        if (NULL == $this->readRoute)
            $this->readRoute = "mp_simplerest_" . $this->resource . "_read";
        if (NULL == $this->deleteRoute)
            $this->deleteRoute = "mp_simplerest_" . $this->resource . "_delete";
        if (NULL == $this->updateRoute)
            $this->updateRoute = "mp_simplerest_" . $this->resource . "_update";
        if (NULL == $this->indexRoute)
            $this->indexRoute = "mp_simplerest_" . $this->resource . "_index";
        if (NULL == $this->countRoute)
            $this->countRoute = "mp_simplerest_" . $this->resource . "_count";
    }

    /**
     * EN : magic getter<br>
     * @param $name
     * @param $arguments
     * @return mixed
     */
    function __get($name)
    {
        $property = lcfirst(substr($name, 3));
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    /**
     * FR : liste une collection<br>
     * EN : list a collection of resources
     * @param Request $req
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    function index(Request $req, Application $app)
    {
        try {
            $limit = $req->query->get("limit", 1000);
            $offset = $req->query->get("offset", 0);
            $criteria = array(); // where
            foreach ($this->criteria as $value) {
                if ($req->query->get($value) != NULL) {
                    $criteria[$value] = $req->query->get($value);
                }
            }
            $order = array(); //order by
            if ($req->query->has("order_by") && $req->query->has("order_order")) {
                $order_by = $req->query->get("order_by");
                $order_order = $req->query->get("order_order", "ASC");
                $choices = array("ASC", "DESC");
                if (in_array($order_order, $choices) && in_array($order_by, $this->criteria)) {
                    $order[$order_by] = $order_order;
                }
            }

            $app["dispatcher"]->dispatch(
                $this->beforeIndex, new GenericEvent($criteria, array("request" => $req, "app" => $app)));
            $collection = $this->service->{$this->findByMethod}(
                $criteria, $order, $limit, $limit * $offset);
            if ($collection instanceof \Traversable) {
                $collection = iterator_to_array($collection,false);
            }
            $app["dispatcher"]->dispatch(
                $this->afterIndex, new GenericEvent($collection, array("request" => $req, "app" => $app)));
            $response = $this->makeResponse($app,
                array("status" => self::SUCCESS,
                    "message" => count($collection) . " $this->resourcePluralize found",
                    "$this->resourcePluralize" => $collection));
        } catch (Exception $e) {
            $message = $this->makeErrorMessage($e);
            $response = $this->makeResponse(
                $app, array("status" => self::OTHER_ERROR, "message" => $message), self::OTHER_ERROR);
        }
        return $response;
    }

    /**
     * FR : lit une resource
     * EN : read a resource
     * @param Request $req
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    function read(Request $req, Application $app, $id)
    {
        try {
            $app["dispatcher"]->dispatch(
                $this->beforeRead, new GenericEvent($id, array("request" => $req, "app" => $app))
            );
            $model = $this->service->{$this->findMethod}($id);
            if ($model == NULL) {
                throw new HttpException(404, "resource $this->resource with id $id not found");
            }
            $app["dispatcher"]->dispatch(
                $this->afterRead, new GenericEvent($model, array("request" => $req, "app" => $app))
            );
            $response = $this->makeResponse($app,
                array("status" => "ok", "$this->resource" => $model));
        } catch (HttpException $e) {
            $message = $this->makeErrorMessage($e);
            $response = $this->makeResponse($app,
                array("status" => self::NOT_FOUND,
                    "message" => $message), self::NOT_FOUND);
        } catch (Exception $e) {
            $message = $this->makeErrorMessage($e);
            $response = $this->makeResponse($app,
                array("status" => self::OTHER_ERROR,
                    "message" => $message), self::OTHER_ERROR);
        }
        return $response;

    }

    /**
     * FR : crée une resource
     * EN : crée une resource
     * @param Request $req
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    function create(Request $req, Application $app, $_format)
    {
        try {
            if ($_format == "json") {
                $data = json_decode($req->getContent(), true);
                if (isset($data[$this->id])) {
                    unset($data[$this->id]);
                }
                $model = new $this->model($data);
            } else {
                $model = $app["serializer"]->deserialize($req->getContent(), $this->model, $_format);
            }
            $app["dispatcher"]->dispatch(
                $this->beforeCreate, new GenericEvent($model, array("request" => $req, "app" => $app)));
            $id = $this->service->{$this->createMethod}($model);
            $app["dispatcher"]->dispatch(
                $this->afterCreate, new GenericEvent($model, array("request" => $req, "app" => $app, "id" => $id)));
            $response = $app->json(array(
                "status" => self::RESOURCE_CREATED,
                "message" => "$this->resource with $this->id $id created with.",
                "id" => $id));
        } catch (Exception $e) {
            $message = $this->makeErrorMessage($e);
            $response = $app->json(array("status" => self::OTHER_ERROR, "message" => $message), self::OTHER_ERROR);
        }
        return $response;
    }

    /**
     * FR: met à jour une resource<br>
     * EN : upate a resource
     * @param Request $req
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    function update(Request $req, Application $app, $id, $_format)
    {
        try {
            $exists = $this->service->{$this->findMethod}($id);
            if ($exists) {
                $data = $app["serializer"]->unserialize($req->getContent(), $_format);
                $data[$this->id] = $id;
                $changes = new $this->model($data);
                $app["dispatcher"]->dispatch(
                    $this->beforeUpdate, new GenericEvent($changes, array("$this->id" => $id, "app" => $app)));
                $rowsAffected = $this->service->{$this->updateMethod}($changes, array("$this->id" => $id));
                $app["dispatcher"]->dispatch(
                    $this->beforeUpdate, new GenericEvent($changes, array("$this->id" => $id, "app" => $app)));
                $response = $this->makeResponse($app,
                    array(
                        "status" => self::SUCCESS,
                        "message" => "$this->resource with $this->id $id updated.",
                        "rowsAffected" => $rowsAffected));
            } else {
                throw new Exception("resource $this->resource not found");
            }
        } catch (Exception $e) {
            $message = $this->makeErrorMessage($e);
            $response = $this->makeResponse($app,
                array("status" => self::OTHER_ERROR, "message" => $message), self::OTHER_ERROR);
        }
        return $response;
    }

    /**
     * FR : efface une resource<br>
     * EN : delete a resource
     * @param Request $req
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    function delete(Request $req, Application $app, $id)
    {
        try {
            $model = $this->service->{$this->findMethod}($id);
            if ($model) {
                $app["dispatcher"]->dispatch(
                    $this->beforeDelete, new GenericEvent($model, array("app" => $app, "request" => $req)));
                $rowsAffected = $this->service->{$this->deleteMethod}($model);
                $app["dispatcher"]->dispatch(
                    $this->afterDelete, new GenericEvent($model, array("app" => $app, "request" => $req)));

                $response = $this->makeResponse($app,
                    array("status" => self::SUCCESS,
                        "message" => "$rowsAffected $this->resourcePluralize deleted.",
                        "rowsAffected" => $rowsAffected), self::SUCCESS);
            } else {
                $response = $this->makeResponse($app,
                    array("status" => self::NOT_FOUND,), self::NOT_FOUND);
            }
        } catch (Exception $e) {
            $message = $this->makeErrorMessage($e);
            $response = $this->makeResponse($app,
                array("status" => self::OTHER_ERROR, "message" => $message), self::OTHER_ERROR);
        }
        return $response;
    }

    function count(Request $req, Application $app)
    {
        try {
            $criteria = array();
            foreach ($this->criteria as $value) {
                if ($req->query->get($value) != NULL) {
                    $criteria[$value] = $req->query->get($value);
                }
            }
            $count = $this->service->{$this->countMethod}($criteria);

            $response = $this->makeResponse($app, array("status" => self::SUCCESS, "count" => $count));
        } catch (Exception $e) {
            $message = $this->makeErrorMessage($e);
            $response = $this->makeResponse($app,
                array("status" => self::OTHER_ERROR, "message" => $message), self::OTHER_ERROR);

        }
        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        $controllers = $app["controllers_factory"];
        /* @var \Silex\ControllerCollection $controllers */
        $this->addCustomRoutes($controllers);
        if (in_array("create", $this->allow))
            $controllers->match("/$this->resource.{_format}", array($this, "create"))
                ->method($this->createVerb)
                ->bind($this->createRoute);
        if (in_array("count", $this->allow))
            $controllers->match("/$this->resource/count.{_format}", array($this, "count"))
                ->method($this->countMethod)
                ->bind($this->countRoute);
        if (in_array("update", $this->allow))
            $controllers->match("/$this->resource/{id}.{_format}", array($this, "update"))
                ->method($this->updateVerb)
                ->bind($this->udpateRoute);
        if (in_array("delete", $this->allow))
            $controllers->match("/$this->resource/{id}.{_format}", array($this, "delete"))
                ->bind($this->deleteRoute)
                ->method($this->deleteVerb);
        if (in_array("index", $this->allow))
            $controllers->match("/$this->resource.{_format}", array($this, "index"))
                ->bind($this->indexRoute)
                ->method($this->indexVerb);
        if (in_array("read", $this->allow))
            $controllers->match("/$this->resource/{id}.{_format}", array($this, "read"))
                ->bind($this->readRoute)
                ->method($this->readVerb);
        $controllers->value("_format", $this->defaultFormat)->assert("_format", implode("|", $this->formats));
        return $controllers;
    }

    public function addCustomRoutes(ControllerCollection $controllers)
    {

    }

    /**
     * FR : selon le format demandé , renvoyer du XML ou du JSON<br>
     * EN : given a format request , return a xml or a json response
     * @param Application $app
     * @param $data
     * @param int $status
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\JsonResponse|Response
     */
    function makeResponse(Application $app, $data, $status = 200, $headers = array())
    {
        $request = $app['request'];
        /* @var Request $request */
        $_format = $request->attributes->get("_format");
        $response = NULL;
        switch ($_format) {
            case "xml":
                array_merge($headers, array("Content-Type" => $app['request']->getMimeType($_format)));
                $response = new Response($app['serializer']->serialize($data, $_format), $status, $headers);
                break;
            default:
                $response = $app->json($data, $status, $headers);
        }
        return $response;
    }


    function makeErrorMessage(Exception $e)
    {
        if ($this->debug) {
            $message = $e->getMessage();
        } else {
            $message = $this->defaultErrorMessage;
        }
        return $message;
    }
}
