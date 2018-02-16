<?php

use Core\App;
use DI\ContainerBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

// Initialisation du container
$builder = new ContainerBuilder();
$builder->addDefinitions(dirname(__DIR__) . '/src/App/config/config.php');
$container = $builder->build();

try {
    // Initialisation de Twig via le Container
    $twig = $container->get(Twig_Environment::class);

    // Initialisation de l'App via le Container
    $app = $container->get(App::class);

    // Lancement de l'App
    $app->run();
} catch (Exception | NotFoundExceptionInterface | ContainerExceptionInterface $e) {
    echo $twig->render('error.twig', ['e' => $e]);
}
