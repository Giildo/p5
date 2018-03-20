<?php

namespace Core\Auth;

use App\Entity\User;
use App\various\appHash;
use Core\Database\Database;
use Core\ORM\Classes\ORMEntity;
use Core\PSR7\HTTPRequest;

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
     * Utilise le même hashage
     * @param User|null $user
     * @return bool
     */
    public function logged(?User $user = null): bool
    {
        $codeVerif = '';

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
     * @param array $results
     * @return void
     */
    public function log(ORMEntity $user, string $password, array &$results): void
    {
        if ($user->password === $this->appHash($password)) {
            $_SESSION['confirmConnect'] = true;
            $_SESSION['user'] = $user;

            $code1 = strlen($user->pseudo);
            $code2 = strlen($user->admin->name);
            $codeVerif = $this->appHash($code1 . $user->pseudo . $user->admin->name . $code2);
            $_SESSION['time'] = $codeVerif;
        } else {
            $results['c_error'] = true;
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
