<?php

namespace PhpUniter\Requester;

use PhpUniter\Requester\Application\Generation\NamespaceGenerator;
use PhpUniter\Requester\Application\Generation\PathCorrector;
use PhpUniter\Requester\Application\Generation\UseGenerator;
use PhpUniter\Requester\Application\Obfuscator\KeyGenerator\RandomMaker;
use PhpUniter\Requester\Application\PhpUnitService;
use PhpUniter\Requester\Application\PhpUnitUserRegisterService;
use PhpUniter\Requester\Application\Placer;
use PhpUniter\Requester\Infrastructure\Integrations\PhpUniterIntegration;
use PhpUniter\Requester\Infrastructure\Integrations\PhpUniterRegistration;
use PhpUniter\Requester\Infrastructure\Repository\UnitTestRepository;
use PhpUniter\Requester\Infrastructure\Request\GenerateClient;
use PhpUniter\Requester\Infrastructure\Request\GenerateRequest;
use PhpUniter\Requester\Infrastructure\Request\RegisterRequest;

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
