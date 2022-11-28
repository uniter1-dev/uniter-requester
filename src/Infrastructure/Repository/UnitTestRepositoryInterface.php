<?php

namespace PhpUniter\PhpUniterRequester\Infrastructure\Repository;

use PhpUniter\PhpUniterRequester\Application\PhpUniter\Entity\PhpUnitTest;

interface UnitTestRepositoryInterface
{
    public function saveOne(PhpUnitTest $unitTest, string $relativePath, string $className): int;
}
