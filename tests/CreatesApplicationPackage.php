<?php

namespace PhpUniter\Requester\Tests;

trait CreatesApplicationPackage
{
    public static function getConfig(): array
    {
        $conf = [];
        require __DIR__.'/../config/config.php';

        return $conf;
    }
}
