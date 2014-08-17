<?php

use Mparaiso\SimpleRest\Controller\Controller;
use Silex\Application;
use Silex\Provider\SerializerServiceProvider;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Serializer\Serializer;

// PHP builtin server router


$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

$autoload = require __DIR__ . '/../../vendor/autoload.php';
$autoload->add("", __DIR__.'/../lib');

$app = new Application(array('debug' => true));

$app->register(new Config());

$app->run();