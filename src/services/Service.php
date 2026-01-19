<?php

abstract class Service
{
    protected function success(array $data = [], string $message = ""): array
    {
        return [
            'status' => 'success',
            'data' => $data,
            'message' => $message
        ];
    }

    protected function error(string $message, int $code = 400): array
    {
        return [
            'status' => 'error',
            'message' => $message,
            'code' => $code
        ];
    }

    protected function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    protected function validate(array $data, array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
}