<?php

namespace Uniter1\UniterRequester\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Uniter1\UniterRequester\Application\Generation\NamespaceGenerator;
use Uniter1\UniterRequester\Tests\CreatesApplicationPackage;

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
        self::assertEquals('Uniter1\UniterRequester\Tests\Unit', $namespace);
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
