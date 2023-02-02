<?php

namespace Uniter1\UniterRequester\Application;

use Uniter1\UniterRequester\Application\File\Exception\DirectoryPathWrong;
use Uniter1\UniterRequester\Application\File\Exception\FileNotAccessed;
use Uniter1\UniterRequester\Application\Generation\Exception\TestNotCreated;
use Uniter1\UniterRequester\Application\PhpUniter\Entity\PhpUnitTest;
use Uniter1\UniterRequester\Infrastructure\Repository\UnitTestRepositoryInterface;

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

    public function getOldTest(string $relativePath, string $className): string
    {
        return $this->repository->getOne($relativePath, $className);
    }
}
