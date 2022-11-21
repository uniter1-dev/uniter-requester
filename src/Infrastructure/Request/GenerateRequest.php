<?php

namespace PhpUniter\Requester\Infrastructure\Request;

use GuzzleHttp\Psr7\Request;

class GenerateRequest extends Request
{
    private string $token;

    public function __construct(string $method, string $uri, array $headers, string $token)
    {
        parent::__construct($method, $uri, $headers);
        $this->token = $token;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
