<?php

namespace PhpUniter\PhpUniterRequester\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PhpUniter\PhpUniterRequester\Application\Generation\NamespaceGenerator;
use PhpUniter\PhpUniterRequester\Tests\CreatesApplicationPackage;

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
        self::assertEquals('PhpUniter\PhpUniterRequester\Tests\Unit', $namespace);
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
