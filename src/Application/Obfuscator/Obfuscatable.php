<?php

namespace PhpUniter\Requester\Application\Obfuscator;

interface Obfuscatable
{
    public function __construct(string $filePath, string $fileBody);

    public function getFilePath(): string;

    public function getFileBody(): string;
}
