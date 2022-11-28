<?php

namespace PhpUniter\Requester\Tests;

trait CreatesApplicationPackage
{
    public function createApplication()
    {
    }

    public static function safeUnlink(string $filePath): bool
    {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    public static function getConfig(): array
    {
        return require_once __DIR__.'/../config/config.php';
    }
}
