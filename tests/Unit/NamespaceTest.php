<?php

namespace PhpUniter\Requester\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpUniter\Requester\Application\Generation\NamespaceGenerator;
use PhpUniter\Requester\Tests\CreatesApplicationPackage;

class NamespaceTest extends TestCase
{
    use CreatesApplicationPackage;

    /**
     * @dataProvider getCases
     *
     * @param string $input
     */
    public function testFindNamespace($input): void
    {
        $namespace = NamespaceGenerator::findNamespace($input);
        self::assertEquals('PhpUniter\Requester\Tests\Unit', $namespace);
    }

    public function getCases(): array
    {
        $fname = __DIR__.'/NamespaceTest.php';

        return [
            [
                file_get_contents($fname),
            ],
        ];
    }
}
