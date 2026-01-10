<?php

require_once 'AppController.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityController extends AppController
{

  private UserRepository $userRepository;

  public function __construct()
  {
    $this->userRepository = new UserRepository();
  }

  public function login()
  {

    if ($this->isPost()) {
      return $this->render('login');
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';


    $user = $this->userRepository->getUserByEmail($email);

    if (!$user) {
      return $this->render('login', ["messages" => "Niepoprawny email lub hasło"]);
    }

    if (!password_verify($password, $user['password'])) {
      return $this->render('login', ["messages" => "Niepoprawny email lub hasło"]);
    }

    //TODO create user session
    //return $this->render('dashboard', ["cards" => []]);

    $url = "http://$_SERVER[HTTP_HOST]";
    header("Location: {$url}/dashboard");
  }

  public function register()
  {
    if ($this->isGet()) {
      return $this->render('register');
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $firstName = $_POST['firstname'] ?? '';
    $lastName = $_POST['lastname'] ?? '';

    if (empty($email) || empty($password) || empty($password2) || empty($firstName) || empty($lastName)) {
      return $this->render('register', ["messages" => "Wszystkie pola są wymagane"]);
    }

    if ($password !== $password2) {
      return $this->render('register', ["messages" => "Podane hasła nie są identyczne"]);
    }

    if ($this->userRepository->getUserByEmail($email)) {
      return $this->render('register', ["messages" => "Użytkownik z podanym adresem email już istnieje"]);
    }

    $this->userRepository->createUser(
      $email,
      password_hash($password, PASSWORD_BCRYPT),
      $firstName,
      $lastName
    );
    return $this->render('login', ["messages" => "Rejestracja przebiegła pomyślnie! Możesz się teraz zalogować."]);
  }
}
