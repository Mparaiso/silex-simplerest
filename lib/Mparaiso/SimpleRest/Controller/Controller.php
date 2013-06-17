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
use Symfony\Component\HttpKernel\Exception\HttpException;

class Controller implements ControllerProviderInterface
{

    public $findByMethod = "findBy";
    protected $findAllMethod = "findAll";
    protected $findMethod = "find";
    protected $createMethod = "create";
    public $updateMethod = "update";
    protected $deleteMethod = "remove";
    protected $id = "id";
    protected $resource;
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
    }

    /**
     * FR magic getter<br>
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
     * FR : liste une collection
     * @param Request $req
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    function index(Request $req, Application $app)
    {
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
        return $app->json($collection);
    }

    /**
     * lit une resource
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
            $response = $app->json($model);
        } catch (HttpException $e) {
            $response = $app->json(
                array("status" => "error", "code" => $e->getStatusCode(), "message" => $e->getMessage()), 404);
        } catch (Exception $e) {
            $response = $app->json(
                array("status" => "error", "message" => $e->getMessage()), 500);
        }
        return $response;

    }

    /**
     * crée une resource
     * @param Request $req
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    function create(Request $req, Application $app)
    {
        try {
            $data = $req->request->all();
            if ($data[$this->id]) {
                unset($data[$this->id]);
            }
//            $model = new $this->model(json_decode($req->getContent(), TRUE));
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
     * met à jour une resource
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
                $data = $req->request->all();
                $data[$this->id] = $id;
                $changes = new $this->model($data);
                $app["dispatcher"]->dispatch(
                    $this->beforeUpdate, new GenericEvent($exists, array("app" => $app, "request" => $req)));
                $rowsAffected = $this->service->{$this->updateMethod}($changes, array("$this->id" => $id));
                $app["dispatcher"]->dispatch(
                    $this->beforeUpdate, new GenericEvent($id, array("app" => $app, "request" => $req)));
                $response = $app->json(
                    array("message" => "ok", "rowsAffected" => $rowsAffected));
            } else {
                throw new Exception("resource $this->resource not found");
            }
        } catch (Exception $e) {
            $response = $app->json(array("status" => "error", "message" => $e->getMessage()), 500);
        }
        return $response;
    }

    /**
     * efface une resource
     * @param Request $req
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    function delete(Request $req, Application $app, $id)
    {
        $model = $this->service->{$this->findMethod}($id);
        if ($model) {
            $app["dispatcher"]->dispatch(
                $this->beforeDelete, new GenericEvent($model, array("app" => $app, "request" => $req)));
            $rowsAffected = $this->service->{$this->deleteMethod}($model);
            $app["dispatcher"]->dispatch(
                $this->afterDelete, new GenericEvent($model, array("app" => $app, "request" => $req)));
        }
        return $app->json(
            array("message" => "ok", "rowsAffected" => $rowsAffected));
    }


    public function connect(Application $app)
    {
        $controllers = $app["controllers_factory"];
        /* @var \Silex\ControllerCollection $controllers */

        $controllers->post("/$this->resource", array($this, "create"));
        $controllers->put("/$this->resource/{id}", array($this, "update"));
        $controllers->delete("/$this->resource/{id}", array($this, "delete"));

        $controllers->get("/$this->resource", array($this, "index"));
        $controllers->get("/$this->resource/{id}", array($this, "read"));

        //$controllers->before('Mparaiso\SimpleRest\Controller\Controller::acceptJson');
        return $controllers;
    }

    static function acceptJson(Request $request)
    {
        if (0 === strpos($request->headers->get("Content-Type"), "application/json")) {
            $data = json_decode($request->getContent(), TRUE);
            $request->request->replace(is_array($data) ? $data : array());
        }
    }
}
