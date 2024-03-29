<?php

namespace Uniter1\UniterRequester\Infrastructure\Integrations;

use Uniter1\UniterRequester\Application\File\Entity\LocalFile;
use Uniter1\UniterRequester\Application\PhpUniter\Entity\PhpUnitTest;
use Uniter1\UniterRequester\Infrastructure\Exception\PhpUnitRegistrationInaccessible;
use Uniter1\UniterRequester\Infrastructure\Exception\PhpUnitTestInaccessible;
use Uniter1\UniterRequester\Infrastructure\Request\GenerateClient;
use Uniter1\UniterRequester\Infrastructure\Request\GenerateRequest;

class PhpUniterIntegration
{
    private GenerateClient $client;
    private GenerateRequest $generateRequest;

    public function __construct(GenerateClient $client, GenerateRequest $generateRequest)
    {
        $this->client = $client;
        $this->generateRequest = $generateRequest;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws PhpUnitRegistrationInaccessible
     * @throws PhpUnitTestInaccessible
     */
    public function generatePhpUnitTest(LocalFile $localFile, bool $inspectorMode, bool $useDependent, string $overwriteOneMethod): PhpUnitTest
    {
        $response = $this->client->send(
            $this->generateRequest,
            [
                'json' => [
                    'class'                => $localFile->getFileBody(),
                    'access_token'         => $this->generateRequest->getToken(),
                    'inspector_mode'       => $inspectorMode,
                    'use_dependent'        => $useDependent,
                    'overwrite_one_method' => $overwriteOneMethod,
                ],
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw new PhpUnitTestInaccessible("Generation failed with error '{$response->getReasonPhrase()}'");
        }

        $generatedTestJson = $response->getBody()->getContents();
        /** @var string[]|string[][] $generatedTest */
        $generatedTest = json_decode($generatedTestJson, true);
        $generatedTestText = $generatedTest['test'];
        /** @var string[] $generatedTestMethods */
        $generatedTestMethods = $generatedTest['test_methods'] ?? [];
        $className = $generatedTest['class'];
        $namespace = $generatedTest['namespace'];

        return new PhpUnitTest(
            $localFile,
            $generatedTestText,
            $className,
            $namespace,
            $generatedTestMethods
        );
    }
}
