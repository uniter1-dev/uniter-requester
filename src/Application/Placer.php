<?php

namespace PhpUniter\PhpUniterRequester\Application;

use PhpUniter\PhpUniterRequester\Application\File\Exception\DirectoryPathWrong;
use PhpUniter\PhpUniterRequester\Application\File\Exception\FileNotAccessed;
use PhpUniter\PhpUniterRequester\Application\Generation\Exception\TestNotCreated;
use PhpUniter\PhpUniterRequester\Application\PhpUniter\Entity\PhpUnitTest;
use PhpUniter\PhpUniterRequester\Infrastructure\Repository\UnitTestRepositoryInterface;

class Placer
{
    private UnitTestRepositoryInterface $repository;

    public function __construct(UnitTestRepositoryInterface $fileRepository)
    {
        $this->repository = $fileRepository;
    }

    /**
     * @param string $relativePath // path from project root to test to write
     *
     * @throws DirectoryPathWrong
     * @throws FileNotAccessed
     * @throws TestNotCreated
     */
    public function placeUnitTest(PhpUnitTest $phpUnitTest, string $relativePath, string $className): int
    {
        return $this->repository->saveOne($phpUnitTest, $relativePath, $className);
    }
}
