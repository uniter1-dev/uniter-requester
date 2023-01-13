<?php

namespace Uniter1\UniterRequester\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Uniter1\UniterRequester\Tests\CreatesApplicationPackage;

class CommandFileWriteTest extends TestCase
{
    use CreatesApplicationPackage;

    public array $container = [];
    private string $pathToTest;
    private string $projectRoot;

    public function setUp(): void
    {
        parent::setUp();
        $conf = self::getConfig();
        $this->pathToTest = (string) $conf['unitTestsDirectory'];
        $this->projectRoot = $conf['basePath'];
    }

    public function testIsWritable()
    {
        self::assertIsWritable($this->projectRoot.'/'.$this->pathToTest);
    }
}
