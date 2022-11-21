<?php

namespace PhpUniter\External;

interface ValidatorInterface
{
    public function fails(): bool;

    public function errors(): array;

    public function setData(array $data): void;
}
