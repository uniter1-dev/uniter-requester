<?php

namespace PhpUniter\Requester\Application\Obfuscator;

use PhpUniter\Requester\Application\File\Entity\LocalFile;
use PhpUniter\Requester\Application\Obfuscator\KeyGenerator\ObfuscateNameMaker;

interface Obfuscated
{
    public function __construct(LocalFile $localFile, ObfuscateNameMaker $keyGenerator, Obfuscator $obfuscator);

    public function getObfuscatedFileBody(): string;

    public function deObfuscate(string $fileBody): string;
}
