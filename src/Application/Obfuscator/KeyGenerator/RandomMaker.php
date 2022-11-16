<?php

namespace PhpUniter\Requester\Application\Obfuscator\KeyGenerator;

class RandomMaker implements ObfuscateNameMaker
{
    public function make(): string
    {
        return 'a'.uniqid();
    }
}
