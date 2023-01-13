<?php

namespace Uniter1\UniterRequester\Application\Obfuscator;

use Uniter1\UniterRequester\Application\File\Entity\LocalFile;
use Uniter1\UniterRequester\Application\Obfuscator\KeyGenerator\ObfuscateNameMaker;

interface Obfuscated
{
    public function __construct(LocalFile $localFile, ObfuscateNameMaker $keyGenerator, Obfuscator $obfuscator);

    public function getObfuscatedFileBody(): string;

    public function deObfuscate(string $fileBody): string;
}
