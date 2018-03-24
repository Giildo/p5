<?php

namespace App\General\Controller;

use App\Controller\AppController;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;

class ErrorController extends AppController implements ControllerInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function notAdmin()
    {
        $this->render('error/notAdmin.twig', []);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function error(): void
    {
        $messageError = 'Message d\'erreur : ' . $_SESSION['flash'];
        unset($_SESSION['flash']);

        $form = new BootstrapForm('col-sm-12 generalForm');
        $form->fieldset('Contacter l\'administrateur du site');
        $form->input('name', 'Nom/Prénom :');
        $form->input('mail', 'EMail :', '', 'email');
        $form->input('errorMessage', ' :', $messageError, 'hidden');
        $form->textarea('message', 'Message :', 10, $messageError);
        $form = $form->submit('Envoyer');

        $this->render('general/error.twig', compact('messageError', 'form'));
    }
}
