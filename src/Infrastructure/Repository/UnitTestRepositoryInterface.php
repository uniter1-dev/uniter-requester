<?php

namespace Uniter1\UniterRequester\Infrastructure\Repository;

use Uniter1\UniterRequester\Application\PhpUniter\Entity\PhpUnitTest;

interface UnitTestRepositoryInterface
{
    public function saveOne(PhpUnitTest $unitTest, string $relativePath, string $className): int;

    public function getOne(string $relativePath, string $className): string;
}
