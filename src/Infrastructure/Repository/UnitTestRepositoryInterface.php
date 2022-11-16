<?php

namespace PhpUniter\Requester\Infrastructure\Repository;

use PhpUniter\Requester\Application\PhpUniter\Entity\PhpUnitTest;

interface UnitTestRepositoryInterface
{
    public function saveOne(PhpUnitTest $unitTest, string $relativePath, string $className): int;
}
