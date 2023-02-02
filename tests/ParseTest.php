<?php

namespace Uniter1\UniterRequester\Tests;

use PhpParser\Node\Stmt\ClassMethod;
use PHPUnit\Framework\TestCase;
use Uniter1\UniterRequester\Application\PhpParser\RequesterParser;

class ParseTest extends TestCase
{
    /**
     * @dataProvider getInputAndExpected
     */
    public function testParse($input)
    {
        $parser = new RequesterParser();
        $tree = $parser::parse($input);
        self::assertIsObject($tree);
    }

    /**
     * @dataProvider getInputAndExpected
     */
    public function testMethods($input)
    {
        $parser = new RequesterParser();
        $tree = $parser::parse($input);
        $methods = $parser::classMethods($tree);

        self::assertIsObject(current($tree));
        foreach ($methods as $method) {
            self::assertInstanceOf(ClassMethod::class, $method);
        }
    }

    /**
     * @dataProvider getInputAndExpected
     */
    public function testPoses($input)
    {
        $parser = new RequesterParser();
        $tree = $parser::parse($input);
        $methods = $parser::classMethods($tree);

        self::assertIsObject(current($tree));
        foreach ($methods as $method) {
            self::assertInstanceOf(ClassMethod::class, $method);
        }
    }

    /**
     * @dataProvider getInputAndExpected
     */
    public function testFetch($input, $expected)
    {
        $parser = new RequesterParser();
        $newCodes = [];
        $newText = $parser::fetch($input, $newCodes, 'testMethod');

        self::assertEquals($expected, $newText);
    }

    public function getCases(): array
    {
        return [
            self::getInputAndExpected(),
        ];
    }

    public static function getInputAndExpected(): array
    {
        return [
            [
                file_get_contents(__DIR__.'/Fixtures/SourceClass.php.input'),
                file_get_contents(__DIR__.'/Fixtures/Replaced.expected'),
            ],
        ];
    }
}
