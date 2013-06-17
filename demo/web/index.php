<?php
/**
 * SimpleRest Demo application
 * @author Mparaiso <mparaiso@online.fr>
 */

$autoload = require __DIR__."/../vendor/autoload.php";

$autoload->add("",__DIR__."/../app");
$autoload->add("",__DIR__."/../../lib");

$app = new App();

$app->run();