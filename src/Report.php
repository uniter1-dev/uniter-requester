<?php

namespace PhpUniter\Requester;

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

    /**
     * @return array
     */
    public function getInfos(): array
    {
        return $this->infos;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
