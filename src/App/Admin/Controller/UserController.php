<?php

namespace App\Admin\Controller;

use App\Admin\Model\UserModel;
use App\Controller\AppController;
use Core\Controller\ControllerInterface;
use Core\Form\BootstrapForm;
use Core\ORM\Classes\ORMException;

class UserController extends AppController implements ControllerInterface
{
    /**
     * @var UserModel
     */
    protected $userModel;

    /**
     * @var UserModel
     */
    protected $adminModel;

    /**
     * Affiche la page de connexion pour un utilisateur
     *
     * @return void
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function login(): void
    {
        $results = [];


        if (!empty($_POST) && isset($_POST['c_pseudo']) && isset($_POST['c_password'])) {
            try {
                $user = $this->select->select([
                    'users' => ['id', 'pseudo', 'firstName', 'lastName', 'mail', 'phone', 'password', 'admin'],
                    'admin' => ['id', 'name']
                ])->from('users')
                    ->singleItem()
                    ->where(['pseudo' => $_POST['c_pseudo']])
                    ->innerJoin('admin', ['users.admin' => 'admin.id'])
                    ->insertEntity(['admin' => 'users'], ['id' => 'admin'], 'oneToMany')
                    ->execute($this->userModel, $this->adminModel);
            } catch (ORMException $e) {
                if ($e->getCode() === ORMException::NO_ELEMENT) {
                    $results['c_error'] = true;
                }
            }

            if (!$results['c_error']) {
                $this->auth->log($user, $_POST['c_password'], $results);
            }
        }

        if (!$this->auth->logged()) {
            $keys = ['c_pseudo', 'c_password', 'r_pseudo', 'firstName', 'lastName', 'mail', 'phone', 'r_password',];

            $post = $this->createPost($keys);

            $this->addUser($results, $post);

            // Création des formulaires de login
            $form1 = new BootstrapForm('col-sm-6 loginForm');
            $form1->fieldset('Connectez-vous');
            ($results['c_error']) ?
                $form1->item("<h4 class='error'>Identifiant ou mot de passe incorrect !</h4>") :
                null;
            $form1->input('c_pseudo', 'Pseudo', $post['c_pseudo']);
            $form1->input('c_password', 'Mot de passe', $post['c_password'], 'password');
            $form1 = $form1->submit('Valider');

            // Création du formulaire pour l'ajout d'utilisateur
            $form2 = new BootstrapForm(('col-sm-6 loginForm'));
            $form2->fieldset('Inscrivez-vous');
            ($results['r_error']) ?
                $form2->item("<h4 class='error'>Tous les champs doivent être renseignés !</h4>") :
                null;
            ($results['r_success']) ?
                $form2->item("<h4 class='success'>Utilisateur ajouté avec succès, veuillez vous connecter.</h4>") :
                null;
            $form2->input('r_pseudo', 'Pseudo', $post['r_pseudo']);
            $form2->input('firstName', 'Prénom', $post['firstName']);
            $form2->input('lastName', 'Nom', $post['lastName'], 'text');
            $form2->input('mail', 'Adresse mail', $post['mail'], 'email');
            $form2->input('phone', 'Téléphone', $post['phone'], 'tel');
            $form2->input('r_password', 'Mot de passe', $post['r_password'], 'password', null, 'new-password');
            $form2 = $form2->submit('Valider');

            $this->render('admin/login.twig', compact('form1', 'form2', 'error'));
        } else {
            $this->redirection('/admin/accueil');
        }
    }

    /**
     * Détruit la variable de connexion
     *
     * @return void
     */
    public function logout(): void
    {
        $this->auth->logout();

        $uri = (isset($_SESSION['paths']['past'])) ? $_SESSION['paths']['past'] : '/accueil';

        $this->redirection($uri);
    }

    /**
     * Vérifie si tous les champs son remplis, si c'est le cas ajoute grâce au UserModel le nouvel utilisateur
     *
     * @param array $results
     * @param array $post
     * @return void
     */
    private function addUser(array &$results, array &$post): void
    {
        if (!empty($_POST) &&
            isset($_POST['r_pseudo']) &&
            isset($_POST['firstName']) &&
            isset($_POST['lastName']) &&
            isset($_POST['mail']) &&
            isset($_POST['phone']) &&
            isset($_POST['r_password'])
        ) {
            if (empty($_POST['r_pseudo']) ||
                empty($_POST['firstName']) ||
                empty($_POST['lastName']) ||
                empty($_POST['mail']) ||
                empty($_POST['phone']) ||
                empty($_POST['r_password'])
            ) {
                $results['r_error'] = true;
            } else {
                $result = $this->userModel->createUser(
                    $_POST['r_pseudo'],
                    $_POST['firstName'],
                    $_POST['lastName'],
                    $_POST['mail'],
                    $_POST['phone'],
                    $_POST['r_password']
                );

                if ($result) {
                    $results['r_success'] = true;

                    $post['c_pseudo'] = $_POST['r_pseudo'];
                    $post['c_password'] = $_POST['r_password'];
                    $post['r_pseudo'] = '';
                    $post['firstName'] = '';
                    $post['lastName'] = '';
                    $post['mail'] = '';
                    $post['phone'] = '';
                    $post['r_password'] = '';
                }
            }
        }
    }
}
