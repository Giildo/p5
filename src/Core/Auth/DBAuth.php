<?php

namespace Core\Auth;

use App\Entity\User;
use Core\Database\Database;
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

    public function logged(): bool
    {
        return ($this->request->getSessionParam('confirmConnect') !== null) ?
            $this->request->getSessionParam('confirmConnect') : false;
    }

    /**
     * Vérifie si le mot de passe est OK, créé la session si OK, sinon renvoie une erreur
     *
     * @param User $user
     * @param string $password
     * @param array $results
     * @return array
     */
    public function log(User $user, string $password, array $results): array
    {
        if ($user->getPassword() === $password) {
            $_SESSION['confirmConnect'] = true;
            $_SESSION['user'] = [
                'id'        => $user->getId(),
                'pseudo'    => $user->getPseudo(),
                'firstName' => $user->getFirstName(),
                'idAdmin'   => $user->getAdmin()
            ];
        } else {
            $results['c_error'] = true;
        }

        return $results;
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
    }

    /**
     * Vérifie que le User est un admin et renvoie true ou false
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $_SESSION['user']['idAdmin'] === '1';
    }
}
