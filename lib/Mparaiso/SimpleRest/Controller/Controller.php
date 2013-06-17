<?php
/**
 * @author mparaiso <mparaiso@online.fr>
 */
namespace Mparaiso\SimpleRest\Controller;


use Exception;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class Controller
 * @package Mparaiso\SimpleRest\Controller
 */
class Controller implements ControllerProviderInterface
{

    public $findByMethod = "findBy";
    protected $findAllMethod = "findAll";
    protected $findMethod = "find";
    protected $createMethod = "create";
    public $updateMethod = "update";
    protected $deleteMethod = "remove";
    protected $id = "id";
    protected $indexVerb = "get";
    protected $readVerb = "get";
    protected $createVerb = "post";
    protected $updateVerb = "post";
    protected $deleteVerb = "delete";

    protected $resource;
    protected $resourcePluralize;
    protected $service;
    protected $model;
    protected $criteria;
    protected $beforeDelete;
    protected $afterDelete;
    protected $beforeRead;
    protected $afterRead;
    protected $beforeCreate;
    protected $afterCreate;
    protected $beforeUpdate;
    protected $afterUpdate;
    protected $beforeIndex;
    protected $afterIndex;


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
            $this->criteria = array_keys(get_class_vars($this->model));
        }
        if ($this->resource && $this->resourcePluralize == NULL) {
            $this->resourcePluralize = $this->resource . "s";
        }
    }

    /**
     * EN : magic getter<br>
     * @param $name
     * @param $arguments
     * @return mixed
     */
    function __call($name, $arguments)
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
            $limit = $req->query->get("limit");
            $offset = $req->query->get("offset");
            $criteria = array();
            foreach ($this->criteria as $value) {
                if ($req->query->get($value) != NULL) {
                    $criteria[$value] = $req->query->get($value);
                }
            }
            $app["dispatcher"]->dispatch(
                $this->beforeIndex, new GenericEvent($criteria, array("request" => $req, "app" => $app)));
            $collection = $this->service->{$this->findByMethod}(
                $criteria, array(), $limit, $limit * $offset);
            $app["dispatcher"]->dispatch(
                $this->afterIndex, new GenericEvent($collection, array("request" => $req, "app" => $app)));
            $response = $this->makeResponse($app,
                array("status" => "ok", "$this->resourcePluralize" => $collection));
        } catch (Exception $e) {
            $response = $this->makeResponse(
                $app, array("status" => "error", "message" => $e->getMessage()), 500);
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
            $response = $this->makeResponse($app,
                array("status"  => "error",
                      "code"    => $e->getStatusCode(),
                      "message" => $e->getMessage()), 404);
        } catch (Exception $e) {
            $response = $this->makeResponse($app,
                array("status"  => "error",
                      "message" => $e->getMessage()), 500);
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
    function create(Request $req, Application $app)
    {
        try {
            $data = json_decode($req->getContent(), TRUE);
            if (isset($data[$this->id])) {
                unset($data[$this->id]);
            }
            $model = new $this->model($data);
            $app["dispatcher"]->dispatch(
                $this->beforeCreate, new GenericEvent($model, array("request" => $req, "app" => $app)));
            $id = $this->service->{$this->createMethod}($model);
            $app["dispatcher"]->dispatch(
                $this->afterCreate, new GenericEvent($model, array("request" => $req, "app" => $app, "id" => $id)));
            $response = $app->json(array("status" => "ok", "id" => $id));
        } catch (Exception $e) {
            $response = $app->json(array("status" => "error", "message" => $e->getMessage()), 500);
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
    function update(Request $req, Application $app, $id)
    {
        try {
            $exists = $this->service->{$this->findMethod}($id);
            if ($exists) {
                $data = json_decode($req->getContent(), TRUE);
                $data[$this->id] = $id;
                $changes = new $this->model($data);
                $app["dispatcher"]->dispatch(
                    $this->beforeUpdate, new GenericEvent($exists, array("app" => $app)));
                $rowsAffected = $this->service->{$this->updateMethod}($changes, array("$this->id" => $id));
                $app["dispatcher"]->dispatch(
                    $this->beforeUpdate, new GenericEvent($id, array("app" => $app)));
                $response = $this->makeResponse($app,
                    array("message" => "ok", "rowsAffected" => $rowsAffected));
            } else {
                throw new Exception("resource $this->resource not found");
            }
        } catch (Exception $e) {
            $response = $this->makeResponse($app,
                array("status" => "error", "message" => $e->getMessage()), 500);
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
            }
            $response = $this->makeResponse($app,
                array("message"      => "ok",
                      "rowsAffected" => $rowsAffected));
        } catch (Exception $e) {
            $response = $this->makeResponse($app,
                array("status" => "error", "message" => $e->getMessage()), 500);
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

        $controllers->match("/$this->resource.{_format}", array($this, "create"))
            ->method($this->createVerb);
        $controllers->match("/$this->resource/{id}.{_format}", array($this, "update"))
            ->method($this->updateVerb);
        $controllers->match("/$this->resource/{id}.{_format}", array($this, "delete"))
            ->method($this->deleteVerb);

        $controllers->match("/$this->resource.{_format}", array($this, "index"))
            ->method($this->indexVerb);
        $controllers->match("/$this->resource/{id}.{_format}", array($this, "read"))
            ->method($this->readVerb);
        $controllers->value("_format", "json")->assert("_format", "xml|json");
        return $controllers;
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


}
