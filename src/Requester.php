<?php

namespace PhpUniter\Requester;

use Conf;
use GuzzleHttp\Exception\GuzzleException;

use PhpUniter\Requester\Application\File\Exception\FileNotAccessed;
use PhpUniter\Requester\Application\Generation\NamespaceGenerator;
use PhpUniter\Requester\Application\Generation\PathCorrector;
use PhpUniter\Requester\Application\Obfuscator\KeyGenerator\RandomMaker;
use PhpUniter\Requester\Application\Obfuscator\ObfuscatorFabric;
use PhpUniter\Requester\Application\Obfuscator\Preprocessor;
use PhpUniter\Requester\Application\PhpUniter\Entity\PhpUnitTest;
use PhpUniter\Requester\Application\PhpUnitService;
use PhpUniter\Requester\Application\Placer;
use PhpUniter\Requester\Infrastructure\Integrations\PhpUniterIntegration;
use PhpUniter\Requester\Infrastructure\Repository\UnitTestRepository;
use PhpUniter\Requester\Infrastructure\Request\GenerateClient;
use PhpUniter\Requester\Infrastructure\Request\GenerateRequest;
use Report;
use Throwable;


class Requester
{
    private Conf $conf;
    private PhpUnitService $phpUnitService;
    private Preprocessor $preprocessor;
    private ObfuscatorFabric $obfuscatorFabric;
    private Report $report;

    /**
     * @param Conf $conf
     * @param Report $report
     */
    public function __construct(Conf $conf, Report $report)
    {
        $this->conf = $conf;
        $generateClient = new GenerateClient();
        $generateRequest = new GenerateRequest(
            'POST',
            $conf::get('php-uniter.baseUrl').'/api/v1/generator/generate',
            [
                'accept'        => ['application/json'],
                'timeout'       => 2,
            ],
            $conf::get('php-uniter.accessToken')
        );
        $phpUniterIntegration = new PhpUniterIntegration($generateClient, $generateRequest);
        $placer = new Placer(new UnitTestRepository($conf::get('php-uniter.projectDirectory')));
        $keyGenerator = new RandomMaker();
        $pathCorrector = new PathCorrector();
        $namespaceGenerator = new NamespaceGenerator($conf::get('php-uniter.baseNamespace'), $conf::get('php-uniter.unitTestsDirectory'), $pathCorrector);
        $this->phpUnitService = new PhpUnitService($phpUniterIntegration, $placer, $keyGenerator, $namespaceGenerator);
        $this->preprocessor = new Preprocessor($conf::get('php-uniter.preprocess'));
        $this->obfuscatorFabric = new ObfuscatorFabric();
        $this->report = $report;
    }

    public function generate($filePath): int
    {
        try {
            chdir($this->conf::get('basePath'));

             if (!is_readable($filePath)) {
                throw new FileNotAccessed("File $filePath was not found");
            }

            try {
                $this->preprocessor->preprocess($filePath);
                $localFile = $this->obfuscatorFabric->createFile($filePath);
                /** @var PhpUnitTest $phpUnitTest */
                $phpUnitTest = $this->phpUnitService->process($localFile, $this->obfuscatorFabric);
                $this->report->info('Generated test was written to '.$phpUnitTest->getPathToTest());
            } catch (GuzzleException $e) {
                $this->report->error($e->getMessage());

                return 1;
            }
        } catch (Throwable $e) {
            $this->report->error($e->getMessage());

            return 1;
        }

        return 0;
    }



}