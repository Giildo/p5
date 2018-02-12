<?php

use App\JojotiqueApp;
use Core\Router\Router;
use DI\ContainerBuilder;

require_once(dirname(__DIR__) . '/vendor/autoload.php');


$builder = new ContainerBuilder();
$builder->addDefinitions(dirname(__DIR__) . '/src/App/config/config.php');
$container = $builder->build();

$app = JojotiqueApp::init($container);

$loader = new Twig_Loader_Filesystem(dirname(__DIR__) . '/views');
$twig = new Twig_Environment($loader, []);

try {
    $router = new Router(
        '\App',
        dirname(__DIR__) . '/src/App/config/routes.xml'
    );
} catch (Exception $e) {
    echo $twig->render('error.twig', ['e' => $e]);
}

try {
    var_dump($router->getRoute('/'));
} catch (Exception $e) {
    echo $e->getMessage(); //Envoyer message sous système de flash
}