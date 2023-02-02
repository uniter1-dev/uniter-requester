<?php

namespace Uniter1\UniterRequester\Infrastructure\Repository;

use Uniter1\UniterRequester\Application\PhpUniter\Entity\PhpUnitTest;

class FakeUnitTestRepository implements UnitTestRepositoryInterface
{
    private array $files = [];

    public function saveOne(PhpUnitTest $unitTest, string $relativePath, string $className): int
    {
        $this->files[$className] = $unitTest->getFinalUnitTest();

        return strlen($this->files[$className]);
    }

    public function save(string $text, string $className): int
    {
        $this->files[$className] = $text;

        return strlen($this->files[$className]);
    }

    public function getOne(string $relativePath, string $className): string
    {
        return $this->files[$className];
    }

    public function getFile(string $className): string
    {
        return $this->files[$className];
    }

    public function list(): array
    {
        return array_keys($this->files);
    }
}
