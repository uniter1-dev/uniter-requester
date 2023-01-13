<?php

namespace Uniter1\UniterRequester\Application\Obfuscator;

interface Obfuscatable
{
    public function __construct(string $filePath, string $fileBody);

    public function getFilePath(): string;

    public function getFileBody(): string;
}
