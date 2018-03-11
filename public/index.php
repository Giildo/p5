<?php

define('ROOT', dirname(__DIR__));

use Core\App;
use Core\Twig\TextExtension;
use DI\ContainerBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

require_once(dirname(__DIR__) . '/vendor/autoload.php');

// Initialisation du container
$builder = new ContainerBuilder();
$builder->addDefinitions(dirname(__DIR__) . '/src/App/config/config.php');
$builder->addDefinitions(dirname(__DIR__) . '/src/App/config/controllerConfig.php');
$builder->addDefinitions(dirname(__DIR__) . '/src/App/config/instanceObject.php');
$container = $builder->build();

$pdo = $container->get(PDO::class);
$results = $pdo->query('SELECT * FROM news WHERE id=4');
$news = $results->fetch();
$results = $pdo->query('SHOW COLUMNS FROM news');
$newsTable = $results->fetchAll();
$newsModel = new \App\Essai\Model\NewsModel($container->get(\Core\Database\Database::class));

$newsOrmTable = new \Core\ORM\Classes\ORMTable('news');
$newsOrmTable->constructWithStdclass($newsTable);
$ormNews = new \App\Entity\News($newsOrmTable);

$ormSelect = new \Core\ORM\Classes\ORMSelect(dirname(__DIR__) . '/src/App/config/orm_config.php');
$listNews = $ormSelect->from('news')
    ->execute($newsModel, $ormNews);

try {
    // Initialisation de Twig via le Container
    $twig = $container->get(Twig_Environment::class);
    $twig->addExtension($container->get(TextExtension::class));

    // Initialisation de l'App via le Container
    $app = $container->get(App::class);

    // Lancement de l'App
    $app->run();
} catch (Exception | NotFoundExceptionInterface | ContainerExceptionInterface $e) {
    echo $twig->render('error.twig', ['e' => $e]);
}
