<?php

namespace App\General\Controller;

use Core\Controller\Controller;

class GeneralController extends Controller
{
    /**
     * Renvoie la page d'accueil
     *
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(): void
    {
        $this->render('general/index.twig', []);
    }

    /**
     * Renvoie une page 404
     *
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function error404(): void
    {
        $this->render('general/404.twig', ['erreur' => 'La page demandée n\'existe pas.']);
    }
}
