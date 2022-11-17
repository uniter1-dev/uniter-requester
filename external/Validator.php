<?php

namespace PhpUniter\External;

class Validator
{

    private array $errors = [];

    public static function make(array $array, array $array1): Validator
    {
        return new self;
    }

    public function fails(): bool
    {
        return false;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @param string[] $messages
     */
    public function setError(array $messages): void
    {
        $this->errors[] = $messages;
    }

}