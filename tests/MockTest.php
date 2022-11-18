<?php

namespace PhpUniter\Requester\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PhpUniter\Requester\Application\Generation\NamespaceGenerator;
use PhpUniter\Requester\Application\Obfuscator\KeyGenerator\StableMaker;
use PhpUniter\Requester\Application\Obfuscator\ObfuscatorFabric;
use PhpUniter\Requester\Application\PhpUnitService;
use PhpUniter\Requester\Application\Placer;
use PhpUniter\Requester\Infrastructure\Integrations\PhpUniterIntegration;
use PhpUniter\Requester\Infrastructure\Repository\FakeUnitTestRepository;
use PhpUniter\Requester\Infrastructure\Repository\UnitTestRepositoryInterface;
use PhpUniter\Requester\Infrastructure\Request\GenerateClient;
use PhpUniter\Requester\Infrastructure\Request\GenerateRequest;
use PhpUniter\Requester\Requester;

class MockTest extends TestCase
{
    public $container = [];

    /**
     * @dataProvider getInputAndExpected
     */
    public function testCommand($input, $obfExpected, $obfTest, $result)
    {
        $requester = new Requester();
        $fakeRepository = new FakeUnitTestRepository();

        $body = json_encode([
            'test'  => $obfTest,
            'code'  => 200,
            'stats' => ['1', '2'],
            'log'   => 'warnings list',
            'class' => 'Foo',
        ]);

        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'], $body),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new GenerateClient(['handler' => $handlerStack]);
        $requester->generateClient = $client;
        $requester->placer = new Placer($fakeRepository);

        $requester->generate(__DIR__.'/Unit/Application/Obfuscator/Entity/Fixtures/SourceClass.php.input');

        $deObfuscatedTest = $fakeRepository->getFile('FooTest.php');

        self::assertEquals($result, $deObfuscatedTest);
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
                file_get_contents(__DIR__.'/Unit/Application/Obfuscator/Entity/Fixtures/SourceClass.php.input'),
                file_get_contents(__DIR__.'/Unit/Application/Obfuscator/Entity/Fixtures/ObfuscatedClass.php.expected'),
                file_get_contents(__DIR__.'/Unit/Application/Obfuscator/Entity/Fixtures/Obfuscated.test.input'),
                file_get_contents(__DIR__.'/Unit/Application/Obfuscator/Entity/Fixtures/Deobfuscated.test.expected'),
            ],
        ];
    }

}
