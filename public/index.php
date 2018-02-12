<?php

use Core\App;
use Core\Router\Router;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$app = App::init();

$loader = new Twig_Loader_Filesystem(dirname(__DIR__) . '/views');
$twig = new Twig_Environment($loader, []);

try {
    $router = new Router(
        '\App',
        dirname(__DIR__) . '/src/App/config/config.xml'
    );
} catch (Exception $e) {
    echo $twig->render('error.twig', ['e' => $e]);
}


try {
    var_dump($router);
} catch (Exception $e) {
    echo $e->getMessage(); //Envoyer message sous système de flash
}