<?php

namespace Uniter1\UniterRequester\Application\Obfuscator\KeyGenerator;

class RandomMaker implements ObfuscateNameMaker
{
    public function make(): string
    {
        return '_a'.uniqid();
    }
}
