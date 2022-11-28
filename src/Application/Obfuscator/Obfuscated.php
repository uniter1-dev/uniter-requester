<?php

namespace PhpUniter\PhpUniterRequester\Application\Obfuscator;

use PhpUniter\PhpUniterRequester\Application\File\Entity\LocalFile;
use PhpUniter\PhpUniterRequester\Application\Obfuscator\KeyGenerator\ObfuscateNameMaker;

interface Obfuscated
{
    public function __construct(LocalFile $localFile, ObfuscateNameMaker $keyGenerator, Obfuscator $obfuscator);

    public function getObfuscatedFileBody(): string;

    public function deObfuscate(string $fileBody): string;
}
