<?php

require_once 'Repository.php';
require_once __DIR__ . '/../models/User.php';

class UserRepository extends Repository
{
    private static $instance;

    public static function getInstance(): UserRepository {
        if (!isset(self::$instance)) {
            self::$instance = new UserRepository();
        }
        return self::$instance;
    }

    public function getUserByEmail(string $email): ?User
    {
        $stmt = $this->database->connect()->prepare('
            SELECT * FROM users WHERE email = :email
        ');
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new User(
            $row['id'],
            $row['firstname'],
            $row['lastname'],
            $row['email'],
            $row['password']
        );
    }

    public function save(User $user): void
    {
        $stmt = $this->database->connect()->prepare('
            INSERT INTO users (firstname, lastname, email, password)
            VALUES (?, ?, ?, ?)
        ');

        $stmt->execute([
            $user->getFirstname(),
            $user->getLastname(),
            $user->getEmail(),
            $user->getPassword()
        ]);
    }
}