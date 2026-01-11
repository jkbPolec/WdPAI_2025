<?php

require_once 'Repository.php';

class UserRepository extends Repository
{

  private static $instance;

  private function __construct() {
    parent::__construct();
  }

  public static function getInstance(): UserRepository {
    if (!isset(self::$instance)) {
      self::$instance = new UserRepository();
    }
    return self::$instance;
  }

  public function getUsers(): ?array
  {

    $query = $this->database->connect()->prepare(
      'SELECT * FROM users'
    );

    $query->execute();

    $users = $query->fetchAll(PDO::FETCH_ASSOC);
    return $users;
  }

  public function getUserByEmail(string $email)
  {
    $query = $this->database->connect()->prepare(
      'SELECT * FROM users WHERE email = :email'
    );
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();

    $user = $query->fetch(PDO::FETCH_ASSOC);
    return $user;
  }

  public function createUser(
    string $email,
    string $hashedPassword,
    string $firstName,
    string $lastName
  ): void {
    $query = $this->database->connect()->prepare(
      'INSERT INTO users (firstname, lastname, email, password)
      VALUES (?,?,?,?)'
    );
    $query->execute([$firstName, $lastName, $email, $hashedPassword]);
  }
}
