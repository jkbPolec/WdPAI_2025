<?php

if (!class_exists('RegisterUserDTO')) {
    class RegisterUserDTO
    {
        public string $email;
        public string $password;
        public string $passwordConfirmation;
        public string $firstname;
        public string $lastname;

        public function __construct(
            string $email,
            string $password,
            string $passwordConfirmation,
            string $firstname,
            string $lastname
        ) {
            $this->email = $email;
            $this->password = $password;
            $this->passwordConfirmation = $passwordConfirmation;
            $this->firstname = $firstname;
            $this->lastname = $lastname;
        }
    }
}