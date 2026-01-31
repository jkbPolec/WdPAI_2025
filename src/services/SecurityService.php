<?php

require_once 'Service.php';
require_once __DIR__ . '/../repository/UserRepository.php';
require_once __DIR__ . '/../dto/LoginDTO.php';
require_once __DIR__ . '/../dto/RegisterUserDTO.php';

class SecurityService extends Service
{
    private $userRepository;

    public function __construct()
    {
        $this->userRepository = UserRepository::getInstance();
    }

    public function login(LoginDTO $data): array
    {
        if (strlen($data->getEmail()) > 100 || strlen($data->getPassword()) > 100) {
            return $this->error("Przekroczono limit długości danych wejściowych.");
        }

        $user = $this->userRepository->getUserByEmail($data->getEmail());

        if (!$user || !password_verify($data->getPassword(), $user->getPassword())) {
            return $this->error("Niepoprawny email lub hasło.");
        }

        return $this->success([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstname' => $user->getFirstname()
        ]);
    }

    public function register(RegisterUserDTO $data): array
    {
        if (
            strlen($data->email) > 100 ||
            strlen($data->password) > 100 ||
            strlen($data->firstname) > 50 ||
            strlen($data->lastname ?? '') > 50
        ) {
            return $this->error("Dane wejściowe przekraczają dozwoloną długość.");
        }

        if (empty($data->email) || empty($data->password) || empty($data->firstname)) {
            return $this->error("Wszystkie pola są wymagane.");
        }

        if ($data->password !== $data->passwordConfirmation) {
            return $this->error("Hasła nie są identyczne.");
        }

        if ($this->userRepository->getUserByEmail($data->email)) {
            return $this->error("Użytkownik już istnieje.");
        }

        try {
            $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);
            $newUser = new User(null, $data->firstname, $data->lastname, $data->email, $hashedPassword);
            $this->userRepository->save($newUser);

            return $this->success([], "Zarejestrowano pomyślnie.");
        } catch (Exception $e) {
            return $this->error("Błąd bazy danych.");
        }
    }
}