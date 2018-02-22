<?php

namespace Core\Controller;

use Core\Auth\DBAuth;
use Core\Entity\EntityInterface;
use Psr\Container\ContainerInterface;
use Twig_Environment;

/**
 * Class Controller
 * @package Core\Controller
 */
class Controller implements ControllerInterface
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DBAuth
     */
    protected $auth;

    /**
     * Controller constructor.
     *
     * @param Twig_Environment $twig
     * @param ContainerInterface $container
     * @param array|null $models
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(Twig_Environment $twig, ContainerInterface $container, ?array $models = [])
    {
        $this->twig = $twig;
        $this->container = $container;

        if (!empty($models)) {
            $this->instantiationModels($models);
        }

        $this->auth = $this->container->get(DBAuth::class);
    }

    /**
     * Lance la méthode passée en paramètres en lui ajoutant si besoin des les paramètres
     *
     * @param string $nameMethod
     * @param array|null $vars
     * @return void
     * @throws \Exception
     */
    public function run(string $nameMethod, ?array $vars = []): void
    {
        if (is_callable([$this, $nameMethod])) {
            $this->$nameMethod($vars);
        } else {
            throw new \Exception('The method called isn\'t a class method');
        }
    }

    /**
     * Envoie une vue Twig pour la page 404
     *
     * @return void
     */
    public function render404(): void
    {
        header('HTTP/1.0 404 Not Found');
        header('Location: /404');
        die();
    }

    /**
     * Envoie une vue Twig pour la page 404
     *
     * @return void
     */
    public function renderNotLog(): void
    {
        header('HTTP/1.1 301 Not Found');
        header('Location: /user/login');
        die();
    }

    public function renderErrorNotAdmin()
    {
        header('HTTP/1.1 301 Not Found');
        header('Location: /error/notAdmin');
        die();
    }

    /**
     * Envoie une vue Twig avec les éléments nécessaire à son traitement
     *
     * @param string $nameView
     * @param array|null $twigVariable
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function render(string $nameView, ?array $twigVariable = []): void
    {
        $twigVariable['sessionConfirmConnect'] = $this->auth->logged();
        $twigVariable['sessionAdmin'] = $_SESSION['user']['idAdmin'] === '1';

        echo $this->twig->render($nameView, $twigVariable);
    }

    /**
     * Méthode de redirection
     */
    public function redirection(string $path): void
    {
        header('HTTP/1.1 301 Not Found');
        header('Location: ' . $path);
        die();
    }

    /**
     * @param array $models
     * @return void
     */
    protected function instantiationModels(array $models): void
    {
        // Implémente les Models nécessaires récupérés depuis la config
        if (!empty($models)) {
            foreach ($models as $key => $model) {
                $key .= "Model";
                $this->$key = $model;
            }
        }
    }

    /**
     * @param array $vars
     * @param int $nbItem
     * @param null|string $optionLimit
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function pagination(array $vars, int $nbItem, ?string $optionLimit = 'blog.limit.post'): array
    {
        $pagination = [];

        $pagination['limit'] = $this->container->get($optionLimit);

        $pagination['id'] = $vars['id'];

        $pagination['pageNb'] = ceil($nbItem / $pagination['limit']);
        $pagination['start'] = ($pagination['limit'] * ($pagination['id'] - 1));

        $pagination['next'] = ($pagination['id'] + 1 <= $pagination['pageNb']) ? $pagination['id'] + 1 : null;
        $pagination['previous'] = ($pagination['id'] - 1 >= 1) ? $pagination['id'] - 1 : null;

        return $pagination;
    }

    /**
     * Génère un tableau qui va regrouper les éléments passés en post ou non
     *
     * @param array $keys
     * @return string[]
     */
    protected function createPost(array $keys): array
    {
        $post = [];

        foreach ($keys as $key) {
            $post[$key] = (isset($_POST[$key])) ? $_POST[$key] : '';
        }

        return $post;
    }

    /**
     * Génère un tableau qui va regrouper les éléments passés en post ou non
     *
     * @param array $keys
     * @param array $posts
     * @param EntityInterface $entity
     * @return string[]
     */
    protected function createPostWithEntity(array $keys, array $posts, EntityInterface $entity): array
    {
        foreach ($keys as $key) {
            $method = 'get' . ucfirst($key);
            $posts[$key] = (empty($posts[$key])) ? $entity->$method() : $posts[$key];
        }

        return $posts;
    }

    /**
     * @param EntityInterface[] $entities
     * @param string $method
     * @return string[]
     */
    protected function createSelectOptions(array $entities, string $method): array
    {
        $selectOptions = [];
        foreach ($entities as $entity) {
            $selectOptions[] = $entity->$method();
        }
        return $selectOptions;
    }
}
