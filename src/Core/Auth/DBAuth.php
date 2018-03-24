<?php

namespace Core\Auth;

use App\Entity\User;
use App\various\appHash;
use Core\Database\Database;
use Core\Exception\JojotiqueException;
use Core\ORM\Classes\ORMEntity;

/**
 * Sert à la gestion de la connexion. Permet de :
 * - connecter un utilisateur
 * - deconnecter l'utilisateur en supprimant les variables de session
 * - vérifier si l'utilisateur est connecté
 * - vérifier si l'utilisateur est administrateur
 * Class DBAuth
 * @uses appHash
 * @package Core\Auth
 */
class DBAuth
{
    /**
     * @var Database
     */
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;

        session_start();
    }

    use appHash;

    /**
     * Récupère l'utilisateur stocké en variable de session.
     * Vérifie que le paramètre reçu n'est pas nul et s'il est bien une instance de notre classe User,
     * --> Vérification pour voir si une personne de l'extérieur n'a pas crée un objet semblable
     * Utilise le appHash pour crée un code de vérification qu'il va comparer à celui stocké en variable de session
     * Si tout est OK, renvoie un "true".
     *
     * @uses logout : À chaque étape si une erreur survient :
     * --> lance logout qui va supprimer les variables stockées en session qui ont un rapport avec la connexion
     * --> retourne "false"
     *
     * @uses appHash : pour vérifier si le jeton stocké en variable de session est le bon
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

        return true;
    }

    /**
     * Vérifie si le mot de passe envoyé en "POST" et celui stocké dans l'utilisateur passé en paramètre sont identiques
     * Si c'est le cas crée :
     * --> une variable de session 'user' qui stockera une instance de "User" avec le mot de passe "Hashé"
     * --> une variable de session 'time' qui stockera un jeton qui permettra de vérifier que le User stocké est le bon
     *
     * @uses appHash : pour "hashé" le mot de passe stocké dans l'utilisateur et le jeton stocké dans la variable 'time'
     *
     * @param ORMEntity|null $user
     * @param null|string $password
     * @return void
     * @throws JojotiqueException
     */
    public function log(?ORMEntity $user = null, ?string $password = null): void
    {
        if (is_null($user)) {
            throw new JojotiqueException("Le nom d'utilisateur doit être renseigné.", JojotiqueException::USER_IS_NULL);
        }

        if (empty($password)) {
            throw new JojotiqueException("Le mot de passe doit être renseigné.", JojotiqueException::PASSWORD_IS_NULL);
        }

        if ($user->password === $password) {
            $user->password = $this->appHash($user->password);
            $_SESSION['user'] = $user;

            $code1 = strlen($user->pseudo);
            $code2 = strlen($user->admin->name);
            $codeVerif = $this->appHash($code1 . $user->pseudo . $user->admin->name . $code2);
            $_SESSION['time'] = $codeVerif;
        } else {
            throw new JojotiqueException("Le mot de passe est incorrect.", JojotiqueException::BAD_PASSWORD);
        }
    }

    /**
     * Supprime les variables de session qui ont été créées lors de la connexion.
     *
     * @return void
     */
    public function logout(): void
    {
        unset($_SESSION['user']);
        unset($_SESSION['time']);
    }

    /**
     * @uses logged : Vérifie que l'utilisateur reçu en paramètre est bien connecté
     * --> Permet d'augmenter la sécurité en passant par toutes les sécurité qu'on retrouve dans la fonction logged.
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
