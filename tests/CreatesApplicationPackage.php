<?php

namespace Uniter1\UniterRequester\Tests;

trait CreatesApplicationPackage
{
    public static function getConfig(): array
    {
        $conf = [];
        require __DIR__.'/../config/config.php';

        return $conf;
    }
}
