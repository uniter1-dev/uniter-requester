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
        $conf = [];
        require __DIR__.'/../config/config.php';
        return $conf;
    }
}
