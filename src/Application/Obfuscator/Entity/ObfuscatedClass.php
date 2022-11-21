<?php

namespace PhpUniter\Requester\Application\Obfuscator\Entity;

use PhpUniter\Requester\Application\File\Entity\LocalFile;
use PhpUniter\Requester\Application\Obfuscator\Exception\ObfuscationFailed;
use PhpUniter\Requester\Application\Obfuscator\KeyGenerator\ObfuscateNameMaker;
use PhpUniter\Requester\Application\Obfuscator\Obfuscated;
use PhpUniter\Requester\Application\Obfuscator\Obfuscator;

class ObfuscatedClass implements Obfuscated
{
    private ObfuscateMap $map;
    private LocalFile $localFile;
    private string $obfuscated = '';
    private ObfuscateNameMaker $keyGenerator;
    private Obfuscator $obfuscator;

    public function __construct(LocalFile $localFile, ObfuscateNameMaker $keyGenerator, Obfuscator $obfuscator)
    {
        $this->localFile = $localFile;
        $this->keyGenerator = $keyGenerator;
        $this->map = new ObfuscateMap();
        $this->obfuscator = $obfuscator;
    }

    /**
     * @throws ObfuscationFailed
     */
    public function getObfuscatedFileBody(): string
    {
        $this->obfuscated = $this->obfuscator->obfuscate($this->map, $this->localFile, [$this, 'getKeySaver']);

        return $this->obfuscated;
    }

    /**
     * @throws ObfuscationFailed
     */
    public function makeObfuscated(): LocalFile
    {
        return new LocalFile($this->localFile->getFilePath(), $this->getObfuscatedFileBody());
    }

    public function deObfuscate(string $fileBody): string
    {
        return $this->obfuscator->deObfuscate($this->map, $fileBody);
    }

    public function getKeySaver(string $mapKey): callable
    {
        return function (array $matches) use ($mapKey) {
            return $this->map->storeKeysAs($mapKey, $matches, $this->getUniqueKey());
        };
    }

    public function getUniqueKey(): string
    {
        return $this->keyGenerator->make();
    }
}
