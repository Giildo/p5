<?php

namespace App\Admin\Model;

use App\Entity\User;
use Core\Model\Model;
use PDO;

class UserModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'users';

    /**
     * Récupère le User correspondant au pseudo et renvoie le résultat de la comparaison avec le password
     * @param string $pseudo
     * @return User|null
     */
    public function comparePass(string $pseudo): ?User
    {
        $result = $this->pdo->prepare('SELECT * FROM users WHERE pseudo = :pseudo');
        $result->bindParam('pseudo', $pseudo);
        $result->setFetchMode(PDO::FETCH_CLASS, User::class);
        $result->execute();

        /** @var User $user */
        $user = $result->fetch();

        if (!$user) {
            return null;
        } else {
            return $user;
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

    public function userByAdmin(int $id): User
    {
        $result = $this->pdo->prepare(
            "SELECT  users.id,
                              users.pseudo,
                              users.firstName,
                              users.lastName,
                              users.mail,
                              users.phone,
                              users.password,
                              admin.name AS admin
                      FROM users
                      LEFT JOIN admin ON users.admin = admin.id
                      WHERE users.id=:id"
        );
        $result->bindParam('id', $id);
        $result->setFetchMode(PDO::FETCH_CLASS, User::class);
        $result->execute();

        return $result->fetch();
    }
}
