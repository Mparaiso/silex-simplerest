<?php
/**
 * FR : Console <br/>
 * @author Mparaiso <mparaiso@online.fr>
 */
$autoload = require __DIR__."/vendor/autoload.php";
$autoload->add("",__DIR__."/app");
$app = new App(array("debug"=>TRUE));
$app->boot(); // !!!! important boot the app before using the console
$console = $app["console"];
$console->run();