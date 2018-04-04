<?php

namespace App\Admin\Controller;

use App\Models\UserModel;
use App\Controller\AppController;
use App\Entities\User;
use Core\Auth\DBAuth;
use Core\Controller\ControllerInterface;
use Core\Exception\JojotiqueException;
use Core\Form\BootstrapForm;
use Exception;
use Jojotique\ORM\Classes\ORMController;
use Jojotique\ORM\Classes\ORMException;
use Jojotique\ORM\Classes\ORMTable;

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
     * Affiche la page de connexion pour un utilisateur.
     * Si "c_pseudo" et "c_password" sont passés en POST c'est qu'il y a une tentative de connexion.
     * Essaye de récupérer dans la BDD l'utilisateur dont le pseudo est passé en POST.
     * S'il n'y arrive pas, envoie une erreur.
     * @uses DBAuth::log() avec l'utilisateur trouvé dans la BDD.
     *
     * Récupère ensuite l'utilisateur stocké en session.
     * @uses DBAuth::logged() avec l'utilisateur récupéré s'il est connecté, pas besoin de charger la page,
     * renvoie vers l'accueil.
     *
     * Sinon,
     *
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws ORMException
     */
    public function login(): void
    {
        $c_error = null;
        $c_errorCode = 0;

        if (!empty($_POST) && isset($_POST['c_pseudo']) && isset($_POST['c_password'])) {
            $user = new User();
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
                    $c_error = "Aucun utilisateur n'a été trouvé avec cet identifiant !";
                }
            }

            try {
                $this->auth->log($user, $_POST['c_password']);
            } catch (Exception $e) {
                $c_error = $e->getMessage();
                $c_errorCode = $e->getCode();
            }
        }

        $user = $this->findUserConnected();

        if (!$this->auth->logged($user)) {
            $r_error = false;
            $r_success = false;
            $r_errorMessage = '';

            if (!empty($_POST) &&
                isset($_POST['pseudo']) &&
                isset($_POST['firstName']) &&
                isset($_POST['lastName']) &&
                isset($_POST['password'])
            ) {
                try {
                    $_POST['admin'] = 'Utilisateur';
                    $user = $this->addUser();
                } catch (ORMException $e) {
                    $r_error = true;
                    $r_errorMessage = $e->getMessage();
                }

                if (!$r_error) {
                    $r_success = true;
                }
            }

            // Création des formulaires de login
            $form1 = new BootstrapForm('col-sm-6 loginForm');
            $form1->fieldset('Connectez-vous');

            if (!is_null($c_error)) {
                $form1->item("<h4 class='error'>{$c_error}</h4>");
            }

            if ($c_errorCode === JojotiqueException::USER_IS_NULL) {
                    $form1->input('c_pseudo', 'Pseudo', $user->pseudo, 'text', 'inputEmpty');
            } else {
                $form1->input('c_pseudo', 'Pseudo', $user->pseudo);
            }

            if ($c_errorCode === JojotiqueException::PASSWORD_IS_NULL) {
                $form1->input('c_password', 'Mot de passe', $user->password, 'password', 'inputEmpty');
            } else {
                $form1->input('c_password', 'Mot de passe', $user->password, 'password');
            }
            $form1 = $form1->submit();

            // Création du formulaire pour l'ajout d'utilisateur
            $form2 = new BootstrapForm(('col-sm-6 loginForm'));
            $form2->fieldset('Inscrivez-vous');
            ($r_error) ?
                $form2->item("<h4 class='error'>{$r_errorMessage}</h4>") :
                null;
            ($r_success) ?
                $form2->item("<h4 class='success'>Utilisateur ajouté avec succès, veuillez vous connecter.</h4>") :
                null;
            $form2->input('pseudo', 'Pseudo', $user->pseudo);
            $form2->input('firstName', 'Prénom', $user->firstName);
            $form2->input('lastName', 'Nom', $user->lastName, 'text');
            $form2->input('mail', 'Adresse mail', $user->mail, 'email');
            $form2->input('phone', 'Téléphone', $user->phone, 'tel');
            $form2->input('password', 'Mot de passe', $user->password, 'password', null, 'new-password');
            $form2 = $form2->submit('Valider');

            $this->render('admin/login.twig', compact('form1', 'form2', 'error'));
        } else {
            $this->redirection(__ROOT__ . '/admin/accueil');
        }
    }

    /**
     * @param array $vars
     * @return void
     * @throws ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function index(array $vars): void
    {
        $userConnected = $this->findUserConnected();

        if ($this->auth->isAdmin($userConnected)) {
            $nbPage = $this->userModel->count();

            $paginationOptions = $this->pagination($vars, $nbPage, 'admin.limit.user');

            $this->paginationMax($paginationOptions, __ROOT__ . '/admin/users/');

            $users = $this->select->select([
                    'users' => ['id', 'pseudo', 'firstName', 'lastName', 'mail', 'phone', 'admin'],
                    'admin' => ['id', 'name']
                ])->from('users')
                ->innerJoin('admin', ['admin.id' => 'users.admin'])
                ->insertEntity(['admin' => 'users'], ['id' => 'admin'], 'manyToMany')
                ->limit($paginationOptions['limit'], $paginationOptions['start'])
                ->execute($this->userModel, $this->adminModel);

            $formCode = [];
            $code1 = strlen($userConnected->pseudo);
            foreach ($users as $user) {
                $code2 = strlen($user->firstName);
                $token = $this->auth->appHash($code2 . $user->firstName . $userConnected->pseudo . $code1);
                $formCode[$user->id] = $token;
            }

            $this->render('admin/users/index.twig', compact('users', 'paginationOptions', 'formCode', 'vars'));
        } else {
            $this->renderErrorNotAdmin();
        }
    }

    /**
     * @param array $vars
     * @throws ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function update(array $vars): void
    {
        if ($this->auth->isAdmin($this->findUserConnected())) {
            $u_success = false;
            $u_error = false;
            $errorMessage = '';

            if (!empty($_POST) &&
                isset($_POST['pseudo']) &&
                isset($_POST['firstName']) &&
                isset($_POST['lastName']) &&
                isset($_POST['admin'])
            ) {
                try {
                    $this->updateUser($vars['id']);
                } catch (ORMException $e) {
                    $u_error = true;
                    $errorMessage = $e->getMessage();
                }

                if (!$u_error) {
                    $u_success = true;
                }
            }

            $user = $this->select->select([
                'users' => ['id', 'pseudo', 'firstName', 'lastName', 'mail', 'phone', 'admin'],
                'admin' => ['id', 'name']
            ])->from('users')
                ->innerJoin('admin', ['admin.id' => 'users.admin'])
                ->insertEntity(['admin' => 'users'], ['id' => 'admin'], 'oneToOne')
                ->where(['users.id' => $vars['id']])
                ->singleItem()
                ->execute($this->userModel, $this->adminModel);

            $admins = $this->select->select(['admin' => ['name']])
                ->from('admin')
                ->execute($this->adminModel);

            $adminsOptions = $this->createSelectOptions($admins, 'name');

            $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');

            if ($u_success) {
                $form->item("<h4 class='success'>Modification réalisée avec succés !</h4>");
            } elseif ($u_error) {
                $form->item("<h4 class='error'>{$errorMessage}</h4>");
            }

            $form->input('pseudo', 'Pseudo', $user->pseudo);
            $form->input('firstName', 'Prénom', $user->firstName);
            $form->input('lastName', 'Nom', $user->lastName);
            $form->input('mail', 'Adresse mail', $user->mail, 'email');
            $form->input('phone', 'Téléphone', $user->phone, 'tel');
            $form->select('admin', $adminsOptions, $user->admin->name, 'Statut');
            $form = $form->submit('Valider');

            $this->render('admin/users/update.twig', compact('user', 'form'));
        } else {
            $this->renderErrorNotAdmin();
        }
    }

    /**
     * @throws ORMException
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function add(): void
    {
        if ($this->auth->isAdmin($this->findUserConnected())) {
            $u_error = false;
            $u_success = false;
            $errorMessage = '';
            $user = new User();

            if (!empty($_POST) &&
                isset($_POST['pseudo']) &&
                isset($_POST['firstName']) &&
                isset($_POST['lastName']) &&
                isset($_POST['admin'])
            ) {
                try {
                    $user = $this->addUser();
                } catch (ORMException $e) {
                    $u_error = true;
                    $errorMessage = $e->getMessage();
                }

                if (!$u_error) {
                    $u_success = true;
                }
            }

            $admins = $this->select->select(['admin' => ['name']])
                ->from('admin')
                ->execute($this->adminModel);

            $adminsOptions = $this->createSelectOptions($admins, 'name');

            $form = new BootstrapForm(' offset-sm-2 col-sm-8 loginForm');

            if ($u_success) {
                $form->item("<h4 class='success'>Ajout réalisé avec succés !</h4>");
            } elseif ($u_error) {
                $form->item("<h4 class='error'>{$errorMessage}</h4>");
            }

            $form->input('pseudo', 'Pseudo', $user->pseudo);
            $form->input('firstName', 'Prénom', $user->firstName);
            $form->input('lastName', 'Nom', $user->lastName);
            $form->input('mail', 'Adresse mail', $user->mail, 'email');
            $form->input('phone', 'Téléphone', $user->phone, 'tel');
            $form->select('admin', $adminsOptions, $user->admin->name, 'Statut');
            $form = $form->submit('Valider');

            $this->render('admin/users/add.twig', compact('form'));
        } else {
            $this->renderErrorNotAdmin();
        }
    }

    /**
     * Vérifie que l'utilisateur est connecté et administrateur.
     * Vérifie que toutes les variables de POST sont présentes.
     * Récupère le "slug" de la catégorie dont l'ID est passé en POST.
     * Crée le hash correspondant au pseudo de l'utilisateur et au slug de la catégorie.
     * Vérifie que le hash correspondent à celui envoyé en POST.
     * Si tout OK supprime la catégorie.
     *
     * @throws ORMException
     * @throws Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function delete(): void
    {
        $userConnected = $this->findUserConnected();

        if ($this->auth->isAdmin($userConnected)) {
            if (!empty($_POST) && isset($_POST['token']) && isset($_POST['id'])) {
                $user = $this->select->select(['users' => ['id', 'firstName']])
                    ->from('users')
                    ->where(['id' => $_POST['id']])
                    ->singleItem()
                    ->execute($this->userModel);

                $code1 = strlen($userConnected->pseudo);
                $code2 = strlen($user->firstName);
                $token = $this->auth->appHash($code2 . $user->firstName . $userConnected->pseudo . $code1);

                if ($token === $_POST['token']) {
                    (new ORMController())->delete($user, $this->userModel);

                    /*Renvoie vers la page d'administration des posts soit
                    --> à la page en cours s'il est envoyé en POST
                    --> sinon vers la 1ère page*/
                    $vars = [];
                    $vars['id'] = (isset($_POST['indexId'])) ? $_POST['id'] : '1';
                    $this->index($vars);
                } else {
                    $this->render('admin/users/index.twig', []);
                    throw new Exception("Une erreur est survenue lors de la suppression de l'utilisaeur,
                        veuillez réessayer.");
                }
            } else {
                $this->render('admin/users/index.twig', []);
                throw new Exception("Une erreur est survenue lors de la suppression de l'utilisaeur,
                    veuillez réessayer.");
            }
        } else {
            $this->renderErrorNotAdmin();
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

        $uri = (isset($_SESSION['paths']['past'])) ? $_SESSION['paths']['past'] : __ROOT__ . '/accueil';

        $this->redirection($uri);
    }

    /**
     * Vérifie si tous les champs son remplis, si c'est le cas ajoute grâce au UserModel le nouvel utilisateur
     *
     * @return User
     * @throws ORMException
     */
    private function addUser(): User
    {
        if (empty($_POST['pseudo'])) {
            throw new ORMException('Le champ "Pseudo" doit être renseigné !');
        }
        if (empty($_POST['firstName'])) {
            throw new ORMException('Le champ "Prénom" doit être renseigné !');
        }
        if (empty($_POST['lastName'])) {
            throw new ORMException('Le champ "Nom" doit être renseigné !');
        }
        if (empty($_POST['admin'])) {
            throw new ORMException('Le champ "Statut" doit être renseigné !');
        }

        $ormTable = new ORMTable('users');
        $ormTable->constructWithStdclass($this->userModel->ORMShowColumns());

        $user = new User($ormTable, true);
        $user->pseudo = $_POST['pseudo'];
        $user->firstName = $_POST['firstName'];
        $user->lastName = $_POST['lastName'];

        $admin = $this->select->select(['admin' => ['id', 'name']])
            ->from('admin')
            ->singleItem()
            ->where(['name' => $_POST['admin']])
            ->execute($this->adminModel);
        $user->adminId = $admin->id;
        $user->admin = $admin;

        $user->password = (isset($_POST['password'])) ? $_POST['password'] : $_POST['pseudo'];

        $user->phone = (isset($_POST['phone'])) ? $_POST['phone'] : null;
        $user->mail = (isset($_POST['mail'])) ? $_POST['mail'] : null;
        $user->setPrimaryKey(['id']);

        (new ORMController())->save($user, $this->userModel);

        return $user;
    }

    /**
     * @param $id
     * @throws ORMException
     */
    private function updateUser($id)
    {
        if (empty($_POST['pseudo'])) {
            throw new ORMException('Le champ "Pseudo" doit être renseigné !');
        }
        if (empty($_POST['firstName'])) {
            throw new ORMException('Le champ "Prénom" doit être renseigné !');
        }
        if (empty($_POST['lastName'])) {
            throw new ORMException('Le champ "Nom" doit être renseigné !');
        }
        if (empty($_POST['admin'])) {
            throw new ORMException('Le champ "Statut" doit être renseigné !');
        }

        $ormTable = new ORMTable('users');
        $ormTable->constructWithStdclass($this->userModel->ORMShowColumns());

        $user = new User($ormTable, true);
        $user->id = $id;
        $user->pseudo = $_POST['pseudo'];
        $user->firstName = $_POST['firstName'];
        $user->lastName = $_POST['lastName'];

        $adminId = $this->select->select(['admin' => ['id']])
            ->from('admin')
            ->singleItem()
            ->where(['name' => $_POST['admin']])
            ->execute($this->adminModel);
        $user->adminId = $adminId->id;

        /** @var User $originalUser */
        $originalUser = $this->select->select(['users' => ['password']])
            ->from('users')
            ->singleItem()
            ->where(['id' => $id])
            ->execute($this->userModel);
        $user->password = $originalUser->getPassword();

        $user->phone = (isset($_POST['phone'])) ? $_POST['phone'] : null;
        $user->mail = (isset($_POST['mail'])) ? $_POST['mail'] : null;
        $user->setPrimaryKey(['id']);

        (new ORMController())->save($user, $this->userModel);
    }
}
