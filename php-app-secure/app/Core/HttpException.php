<?php

namespace App\Core;

use Exception;

class HttpException extends Exception
{
    protected int $statusCode;
    protected string $userMessage;

    public function __construct(int $statusCode, string $message = '', ?string $userMessage = null)
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->userMessage = $userMessage ?? 'The requested page is not available right now.';
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }
}
