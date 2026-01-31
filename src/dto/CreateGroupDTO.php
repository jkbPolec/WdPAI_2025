<?php

if (!class_exists('CreateGroupDTO')) {
    class CreateGroupDTO
    {
        public string $name;
        public string $description;
        public array $memberEmails;

        public function __construct(string $name, ?string $description, string $membersJson)
        {
            $this->name = $name;
            $this->description = $description ?? '';
            $this->memberEmails = json_decode($membersJson ?: '[]', true) ?: [];
        }
    }
}