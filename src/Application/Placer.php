<?php

namespace PhpUniter\Requester\Application;

use PhpUniter\Requester\Application\File\Exception\DirectoryPathWrong;
use PhpUniter\Requester\Application\File\Exception\FileNotAccessed;
use PhpUniter\Requester\Application\Generation\Exception\TestNotCreated;
use PhpUniter\Requester\Application\PhpUniter\Entity\PhpUnitTest;
use PhpUniter\Requester\Infrastructure\Repository\UnitTestRepositoryInterface;

class Placer
{
    private UnitTestRepositoryInterface $repository;

    public function __construct(UnitTestRepositoryInterface $fileRepository)
    {
        $this->repository = $fileRepository;
    }

    /**
     * @throws DirectoryPathWrong
     * @throws FileNotAccessed
     * @throws TestNotCreated
     *
     * @param string $relativePath // path from project root to test to write
     */
    public function placeUnitTest(PhpUnitTest $phpUnitTest, string $relativePath, string $className): int
    {
        return $this->repository->saveOne($phpUnitTest, $relativePath, $className);
    }
}
