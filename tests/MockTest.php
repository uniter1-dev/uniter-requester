<?php

namespace PhpUniter\Requester\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use PhpUniter\Requester\Application\Generation\NamespaceGenerator;
use PhpUniter\Requester\Application\Generation\PathCorrector;
use PhpUniter\Requester\Application\Obfuscator\KeyGenerator\StableMaker;
use PhpUniter\Requester\Application\PhpUnitService;
use PhpUniter\Requester\Application\Placer;
use PhpUniter\Requester\Infrastructure\Integrations\PhpUniterIntegration;
use PhpUniter\Requester\Infrastructure\Repository\FakeUnitTestRepository;
use PhpUniter\Requester\Infrastructure\Request\GenerateClient;
use PhpUniter\Requester\Infrastructure\Request\GenerateRequest;
use PhpUniter\Requester\Requester;
use PhpUniter\Requester\Tests\Unit\UpdateExpected;

class MockTest extends TestCase
{
    use UpdateExpected;
    public $container = [];

    /**
     * @dataProvider getInputAndExpected
     */
    public function testRequesterGenerate($input, $obfExpected, $obfTest, $result)
    {
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
        $requester = new Requester();

        $generateRequest = new GenerateRequest(
            'POST',
            $requester->conf->get('baseUrl').$requester->conf->get('generationPath'),
            [
                'accept'        => ['application/json'],
                'timeout'       => 2,
            ],
            $requester->conf->get('accessToken')
        );

        $handlerStack = HandlerStack::create($mock);
        $client = new GenerateClient(['handler' => $handlerStack]);

        $phpUniterIntegration = new PhpUniterIntegration($client, $generateRequest);
        $keyGenerator = new StableMaker();
        $pathCorrector = new PathCorrector();
        $namespaceGenerator = new NamespaceGenerator($requester->conf->get('baseNamespace'), $requester->conf->get('unitTestsDirectory'), $pathCorrector);
        $requester->phpUnitService = new PhpUnitService($phpUniterIntegration, new Placer($fakeRepository), $keyGenerator, $namespaceGenerator);

        $res = $requester->generate(__DIR__.'/Unit/Application/Obfuscator/Entity/Fixtures/SourceClass.php.input');
        $requestObfuscatedText = $requester->getPhpUnitTest()->getObfuscatedUnitTest();

        $deObfuscatedTest = $fakeRepository->getFile('FooTest.php');

        self::actualize(__DIR__.'/Unit/Application/Obfuscator/Entity/Fixtures/Obfuscated.test.input', $requestObfuscatedText, true);
        self::actualize(__DIR__.'/Unit/Application/Obfuscator/Entity/Fixtures/Deobfuscated.test.expected', $deObfuscatedTest, true);

        self::assertEquals(0, $res);
        self::assertEquals($obfTest, $requestObfuscatedText);
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
