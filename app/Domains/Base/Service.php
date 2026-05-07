<?php

namespace App\Domains\Base;

abstract class Service
{
    protected function error(string $message, int $code = 400): void
    {
        throw new \Exception($message, $code);
    }

    protected function success(mixed $data = null, string $message = 'Operation successful'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }
}