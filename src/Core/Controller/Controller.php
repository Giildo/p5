<?php

namespace Core\Controller;

use Core\Auth\DBAuth;
use Core\Exception\JojotiqueException;
use Core\ORM\Classes\ORMSelect;
use Psr\Container\ContainerInterface;
use Twig_Environment;

/**
 * Classes Controller
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
     * @var ORMSelect
     */
    protected $select;

    /**
     * Controller constructor.
     *
     * @param Twig_Environment $twig
     * @param ContainerInterface $container
     * @param array|null $models
     * @param ORMSelect $select
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(Twig_Environment $twig, ContainerInterface $container, ?array $models = [], ?ORMSelect $select = null)
    {
        $this->twig = $twig;
        $this->container = $container;

        if (!empty($models)) {
            $this->instantiationModels($models);
        }

        $this->auth = $this->container->get(DBAuth::class);
        $this->select = $select;
    }

    /**
     * Lit la méthode récupérée dans la route, vérifie que celle-ci est bien présente dans le contrôleur,
     * sinon renvoie une erreur.
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
            $className = get_class($this);
            throw new JojotiqueException("\"{$nameMethod}\" n'est pas une méthode de \"{$className}\"", JojotiqueException::ROUTE_METHOD_ERROR);
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
        echo $this->twig->render($nameView, $twigVariable);
    }

    /**
     * Méthode de redirection
     *
     * @param string $path
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
     * @param ORMSelect $select
     */
    public function setSelect(ORMSelect $select): void
    {
        $this->select = $select;
    }
}
