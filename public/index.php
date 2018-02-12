<?php

use Core\App;
use Core\Router\Router;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$app = App::init();

$router = new Router(
    '\App',
    dirname(__DIR__) . '/src/App/config/config.xml'
);

$loader = new Twig_Loader_Filesystem(dirname(__DIR__) . '/views');
$twig = new Twig_Environment($loader, []);
echo $twig->render('layout.twig');

try {
    var_dump($router->getRoute($_SERVER['REQUEST_URI']));
} catch (Exception $e) {
    echo $e->getMessage(); //Envoyer message sous système de flash
}