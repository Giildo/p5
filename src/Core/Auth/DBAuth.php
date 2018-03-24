<?php

namespace Core\Auth;

use App\Admin\Model\UserModel;
use App\Entity\User;
use App\various\appHash;
use Core\Database\Database;
use Core\ORM\Classes\ORMEntity;
use Core\PSR7\HTTPRequest;
use Exception;

class DBAuth
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var HTTPRequest
     */
    private $request;

    public function __construct(Database $database, HTTPRequest $request)
    {
        $this->database = $database;
        $this->request = $request;

        session_start();
    }

    use appHash;

    /**
     * Récupère en paramètre l'utilisateur qui est censé être connecté avec l'ID stocké en SESSION
     * Vérifie que le paramètre reçu n'est pas nul et s'il est bien une instance de notre classe User,
     * --> Vérification pour voir si une personne de l'extérieur n'a pas crée un objet semblable
     * Utilise le même hashage pour crée un code de vérification qu'il va comparer à celui en SESSION
     * Si tout est OK, renvoie un true stocké normalement dans la SESSION.
     *
     * @param User|null $user
     * @return bool
     */
    public function logged(?User $user = null): bool
    {
        $codeVerif = '';

        if (is_null($user) || !($user instanceof User)) {
            $this->logout();
            return false;
        }

        if (!is_null($user)) {
            $code1 = strlen($user->pseudo);
            $code2 = strlen($user->admin->name);
            $codeVerif = $this->appHash($code1 . $user->pseudo . $user->admin->name . $code2);
        }

        if (!isset($_SESSION['time']) || $codeVerif !== $_SESSION['time']) {
            $this->logout();
            return false;
        }

        return (isset($_SESSION['confirmConnect'])) ? $_SESSION['confirmConnect']: false;
    }

    /**
     * Vérifie si le mot de passe est OK, créé la session si OK, sinon renvoie une erreur
     *
     * @param ORMEntity $user
     * @param string $password
     * @return void
     * @throws Exception
     */
    public function log(ORMEntity $user, string $password): void
    {
        if ($user->password === $password) {
            $_SESSION['confirmConnect'] = true;

            $user->password = $this->appHash($user->password);
            $_SESSION['user'] = $user;

            $code1 = strlen($user->pseudo);
            $code2 = strlen($user->admin->name);
            $codeVerif = $this->appHash($code1 . $user->pseudo . $user->admin->name . $code2);
            $_SESSION['time'] = $codeVerif;
        } else {
            throw new Exception("Le mot de passe est incorrect !");
        }
    }

    /**
     * Supprime les variables de session qui ont été créées lors de la connexion
     *
     * @return void
     */
    public function logout(): void
    {
        unset($_SESSION['confirmConnect']);
        unset($_SESSION['user']);
        unset($_SESSION['time']);
    }

    /**
     * @uses $this->logged() Vérifie que le User est connecté
     * puis vérifie qu'il est admin et renvoie le résultat de la vérification.
     *
     * @param User|null $user
     * @return bool
     */
    public function isAdmin(?User $user = null): bool
    {
        if ($this->logged($user)) {
            return $_SESSION['user']->admin->id === 1;
        }

        return false;
    }
}
