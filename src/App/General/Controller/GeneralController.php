<?php

namespace App\General\Controller;

use App\Controller\AppController;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;

class GeneralController extends AppController implements ControllerInterface
{
    /**
     * Renvoie la page d'accueil
     *
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(): void
    {
        $form = new BootstrapForm('offset-sm-2 col-sm-8 generalForm');
        $form->fieldset('Laissez-nous un message');
        $form->input('name', 'Nom/Prénom :');
        $form->input('mail', 'EMail :', '', 'email');
        $form->textarea('message', 'Message :');
        $form = $form->submit('Envoyer');

        $this->render('general/index.twig', compact('form'));
    }

    /**
     * Renvoie une page 404
     *
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function error404(): void
    {
        $this->render('general/404.twig', ['erreur' => 'La page demandée n\'existe pas.']);
    }
}
