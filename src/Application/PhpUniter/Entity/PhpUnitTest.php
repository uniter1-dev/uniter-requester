<?php

namespace Uniter1\UniterRequester\Application\PhpUniter\Entity;

use Uniter1\UniterRequester\Application\File\Entity\LocalFile;

class PhpUnitTest
{
    private LocalFile $localFile;
    private string $obfuscatedUnitTest;
    private string $finalUnitTest = '';
    private array $repositories;
    private string $pathToTest = '';
    private array $testMethods;
    private string $className;
    private string $namespace;

    public function __construct(LocalFile $localFile, string $unitTest, string $className, string $namespace, array $testMethods = [], array $repositories = [])
    {
        $this->localFile = $localFile;
        $this->obfuscatedUnitTest = $unitTest;
        $this->repositories = $repositories;
        $this->testMethods = $testMethods;
        $this->className = $className;
        $this->namespace = $namespace;
    }

    public function getObfuscatedUnitTest(): string
    {
        return $this->obfuscatedUnitTest;
    }

    public function getLocalFile(): LocalFile
    {
        return $this->localFile;
    }

    public function getFinalUnitTest(): string
    {
        return $this->finalUnitTest;
    }

    public function setFinalUnitTest(string $finalUnitTest): void
    {
        $this->finalUnitTest = $finalUnitTest;
    }

    public function getPathToTest(): string
    {
        return $this->pathToTest;
    }

    public function setPathToTest(string $pathToTest): void
    {
        $this->pathToTest = $pathToTest;
    }

    public function getRepositories(): array
    {
        return $this->repositories;
    }

    public function getTestMethods(): array
    {
        return $this->testMethods;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }
}
