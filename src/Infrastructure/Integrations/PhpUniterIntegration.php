<?php

namespace PhpUniter\PhpUniterRequester\Infrastructure\Integrations;

use PhpUniter\PhpUniterRequester\Application\File\Entity\LocalFile;
use PhpUniter\PhpUniterRequester\Application\PhpUniter\Entity\PhpUnitTest;
use PhpUniter\PhpUniterRequester\Infrastructure\Exception\PhpUnitRegistrationInaccessible;
use PhpUniter\PhpUniterRequester\Infrastructure\Exception\PhpUnitTestInaccessible;
use PhpUniter\PhpUniterRequester\Infrastructure\Request\GenerateClient;
use PhpUniter\PhpUniterRequester\Infrastructure\Request\GenerateRequest;

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
    public function generatePhpUnitTest(LocalFile $localFile): PhpUnitTest
    {
        $response = $this->client->send(
            $this->generateRequest,
            [
                'json' => [
                    'class'          => $localFile->getFileBody(),
                    'access_token'   => $this->generateRequest->getToken(),
                ],
            ]
        );

        if (200 !== $response->getStatusCode()) {
            throw new PhpUnitTestInaccessible("Generation failed with error '{$response->getReasonPhrase()}'");
        }

        $generatedTestJson = $response->getBody()->getContents();
        /** @var string[] $generatedTest */
        $generatedTest = json_decode($generatedTestJson, true);
        $generatedTestText = $generatedTest['test'];

        return new PhpUnitTest(
            $localFile,
            $generatedTestText,
            $generatedTest
        );
    }
}
