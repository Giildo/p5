<?php

namespace App\Admin\Model;

use App\Entity\User;
use Core\Model\Model;
use PDO;

class UserModel extends Model
{
    /**
     * Récupère le User correspondant au pseudo et renvoie le résultat de la comparaison avec le password
     * @param string $pseudo
     * @param string $password
     * @return bool
     */
    public function comparePass(string $pseudo, string $password): bool
    {
        $result = $this->pdo->prepare('SELECT * FROM users WHERE pseudo = :pseudo');
        $result->bindParam('pseudo', $pseudo);
        $result->setFetchMode(PDO::FETCH_CLASS, User::class);
        $result->execute();

        /** @var User $user */
        $user = $result->fetch();

        if (!$user) {
            return false;
        } else {
            return $user->getPassword() === $password;
        }
    }

    /**
     * Créé un User dans DB
     *
     * @param string $r_pseudo
     * @param string $firstName
     * @param string $lastName
     * @param string $mail
     * @param string $phone
     * @param string $r_password
     * @return bool
     */
    public function createUser(
        string $r_pseudo,
        string $firstName,
        string $lastName,
        string $mail,
        string $phone,
        string $r_password
    ): bool {
        $result = $this->pdo->prepare(
            'INSERT INTO `users`
                      (`pseudo`, `firstName`, `lastName`, `mail`, `phone`, `password`)
                      VALUES (:pseudo, :firstName, :lastName, :mail, :phone, :pass)'
        );
        $result->bindParam('pseudo', $r_pseudo);
        $result->bindParam('firstName', $firstName);
        $result->bindParam('lastName', $lastName);
        $result->bindParam('mail', $mail);
        $result->bindParam('phone', $phone);
        $result->bindParam('pass', $r_password);
        return $result->execute();
    }
}
