<?php

namespace Uniter1\UniterRequester;

use Uniter1\UniterRequester\Application\Generation\NamespaceGenerator;
use Uniter1\UniterRequester\Application\Generation\PathCorrector;
use Uniter1\UniterRequester\Application\Generation\UseGenerator;
use Uniter1\UniterRequester\Application\Obfuscator\KeyGenerator\RandomMaker;
use Uniter1\UniterRequester\Application\PhpUnitService;
use Uniter1\UniterRequester\Application\PhpUnitUserRegisterService;
use Uniter1\UniterRequester\Application\Placer;
use Uniter1\UniterRequester\Infrastructure\Integrations\PhpUniterIntegration;
use Uniter1\UniterRequester\Infrastructure\Integrations\PhpUniterRegistration;
use Uniter1\UniterRequester\Infrastructure\Repository\UnitTestRepository;
use Uniter1\UniterRequester\Infrastructure\Request\GenerateClient;
use Uniter1\UniterRequester\Infrastructure\Request\GenerateRequest;
use Uniter1\UniterRequester\Infrastructure\Request\RegisterRequest;

class RequesterFactory
{
    public static function registerServiceFactory(array $config)
    {
        $registerRequest = new RegisterRequest(
            'POST',
            $config['baseUrl'].$config['registrationPath'],
            [
                'accept'        => ['application/json'],
                'timeout'       => 2,
            ]
        );

        $generateClient = new GenerateClient();
        $registration = new PhpUniterRegistration($generateClient, $registerRequest);

        return new PhpUnitUserRegisterService($registration);
    }

    public static function generateServiceFactory(array $config)
    {
        $generateClient = new GenerateClient();
        $generateRequest = new GenerateRequest(
            'POST',
            $config['baseUrl'].$config['generationPath'],
            [
                'accept'        => ['application/json'],
                'timeout'       => 2,
            ],
            $config['accessToken']
        );

        $phpUniterIntegration = new PhpUniterIntegration($generateClient, $generateRequest);
        $placer = new Placer(new UnitTestRepository($config['projectDirectory']));
        $keyGenerator = new RandomMaker();
        $pathCorrector = new PathCorrector();
        $useGenerator = new UseGenerator($config['helperClass']);
        $namespaceGenerator = new NamespaceGenerator($config['baseNamespace'], $config['unitTestsDirectory'], $pathCorrector);

        return new PhpUnitService($phpUniterIntegration, $placer, $keyGenerator, $namespaceGenerator, $useGenerator);
    }
}
