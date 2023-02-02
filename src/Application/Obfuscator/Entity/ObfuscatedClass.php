<?php

namespace Uniter1\UniterRequester\Application\Obfuscator\Entity;

use Uniter1\UniterRequester\Application\File\Entity\LocalFile;
use Uniter1\UniterRequester\Application\Obfuscator\Exception\ObfuscationFailed;
use Uniter1\UniterRequester\Application\Obfuscator\KeyGenerator\ObfuscateNameMaker;
use Uniter1\UniterRequester\Application\Obfuscator\Obfuscated;
use Uniter1\UniterRequester\Application\Obfuscator\Obfuscator;

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

    /**
     * @throws ObfuscationFailed
     */
    public function deObfuscate(string $fileBody): string
    {
        return $this->obfuscator->deObfuscate($this->map, $fileBody);
    }

    /**
     * @throws ObfuscationFailed
     */
    public function deObfuscateMethods(array $methods): array
    {
        $res = [];
        foreach ($methods as $name => $method) {
            $newName = $this->obfuscator->deObfuscate($this->map, $name);
            $res[$newName] = $this->obfuscator->deObfuscate($this->map, $method);
        }

        return $res;
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
