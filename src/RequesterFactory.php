<?php

namespace PhpUniter\PhpUniterRequester;

use PhpUniter\PhpUniterRequester\Application\Generation\NamespaceGenerator;
use PhpUniter\PhpUniterRequester\Application\Generation\PathCorrector;
use PhpUniter\PhpUniterRequester\Application\Generation\UseGenerator;
use PhpUniter\PhpUniterRequester\Application\Obfuscator\KeyGenerator\RandomMaker;
use PhpUniter\PhpUniterRequester\Application\PhpUnitService;
use PhpUniter\PhpUniterRequester\Application\PhpUnitUserRegisterService;
use PhpUniter\PhpUniterRequester\Application\Placer;
use PhpUniter\PhpUniterRequester\Infrastructure\Integrations\PhpUniterIntegration;
use PhpUniter\PhpUniterRequester\Infrastructure\Integrations\PhpUniterRegistration;
use PhpUniter\PhpUniterRequester\Infrastructure\Repository\UnitTestRepository;
use PhpUniter\PhpUniterRequester\Infrastructure\Request\GenerateClient;
use PhpUniter\PhpUniterRequester\Infrastructure\Request\GenerateRequest;
use PhpUniter\PhpUniterRequester\Infrastructure\Request\RegisterRequest;

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
