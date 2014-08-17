<?php

use Mparaiso\Provider\ConsoleServiceProvider;
use Silex\Application;

$autoload = require __DIR__ . '/vendor/autoload.php';
$autoload->add("", __DIR__.'/../lib');
$autoload->add("", __DIR__.'/lib');

$app = new Application(array('debug' => true));

$app->register(new ConsoleServiceProvider());

$app->register(new Config());

$app->boot();

$app->flush();

$app['console']->run();