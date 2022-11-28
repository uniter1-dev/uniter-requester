<?php

namespace PhpUniter\PhpUniterRequester;

class Report
{
    private array $infos = [];
    private array $errors = [];

    public function info(string $message): void
    {
        $this->infos[] = $message;
    }

    public function error(string $message): void
    {
        $this->errors[] = $message;
    }

    public function getInfos(): array
    {
        return $this->infos;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
