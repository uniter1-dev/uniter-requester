<?php

namespace PhpUniter\Requester\Tests\Unit;

trait UpdateExpected
{
    public static function actualize(string $path, string $actual, $doIt = false): void
    {
        if ($doIt) {
            $done = self::updateExpected($path, $actual);
        }
    }

    public static function updateExpected(string $path, string $actual)
    {
        return file_put_contents($path, $actual);
    }
}