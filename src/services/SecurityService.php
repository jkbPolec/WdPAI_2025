<?php

require_once 'Service.php';
require_once __DIR__ . '/../repository/UserRepository.php';

class SecurityService extends Service
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = UserRepository::getInstance();
    }

    public function login(array $data): array
    {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->error("Email i hasło są wymagane.");
        }

        $user = $this->userRepository->getUserByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->error("Niepoprawny email lub hasło.");
        }

        return $this->success([
            'id' => $user['id'],
            'email' => $user['email'],
            'firstname' => $user['firstname']
        ]);
    }

    public function register(array $data): array
    {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $password2 = $data['password2'] ?? '';
        $firstName = $data['firstname'] ?? '';
        $lastName = $data['lastname'] ?? '';

        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            return $this->error("Wszystkie pola są wymagane.");
        }

        if ($password !== $password2) {
            return $this->error("Podane hasła nie są identyczne.");
        }

        if ($this->userRepository->getUserByEmail($email)) {
            return $this->error("Użytkownik z tym mailem już istnieje.");
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $this->userRepository->createUser($email, $hashedPassword, $firstName, $lastName);
            return $this->success([], "Rejestracja pomyślna! Możesz się zalogować.");
        } catch (Exception $e) {
            return $this->error("Błąd podczas rejestracji.");
        }
    }
}