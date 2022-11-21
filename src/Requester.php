<?php

namespace PhpUniter\Requester;

use GuzzleHttp\Exception\GuzzleException;
use PhpUniter\External\Conf;
use PhpUniter\External\Report;
use PhpUniter\External\ValidationException;
use PhpUniter\External\Validator;
use PhpUniter\External\ValidatorInterface;
use PhpUniter\Requester\Application\File\Exception\FileNotAccessed;
use PhpUniter\Requester\Application\Generation\NamespaceGenerator;
use PhpUniter\Requester\Application\Generation\PathCorrector;
use PhpUniter\Requester\Application\Obfuscator\KeyGenerator\RandomMaker;
use PhpUniter\Requester\Application\Obfuscator\ObfuscatorFabric;
use PhpUniter\Requester\Application\Obfuscator\Preprocessor;
use PhpUniter\Requester\Application\PhpUniter\Entity\PhpUnitTest;
use PhpUniter\Requester\Application\PhpUnitService;
use PhpUniter\Requester\Application\PhpUnitUserRegisterService;
use PhpUniter\Requester\Application\Placer;
use PhpUniter\Requester\Infrastructure\Integrations\PhpUniterIntegration;
use PhpUniter\Requester\Infrastructure\Integrations\PhpUniterRegistration;
use PhpUniter\Requester\Infrastructure\Repository\UnitTestRepository;
use PhpUniter\Requester\Infrastructure\Request\GenerateClient;
use PhpUniter\Requester\Infrastructure\Request\GenerateRequest;
use PhpUniter\Requester\Infrastructure\Request\RegisterRequest;

class Requester
{
    public Conf $conf;
    public PhpUnitService $phpUnitService;
    public Preprocessor $preprocessor;
    public ObfuscatorFabric $obfuscatorFabric;
    public Report $report;
    public PhpUnitUserRegisterService $registerService;
    private PhpUnitTest $phpUnitTest;

    /**
     * @param Conf   $conf
     * @param Report $report
     */
    public function __construct(?Conf $conf = null, ?Report $report = null)
    {
        $this->conf = $conf ?? new Conf();
        $this->report = $report ?? new Report();

        $generateClient = new GenerateClient();

        $generateRequest = new GenerateRequest(
            'POST',
            $this->conf->get('baseUrl').$this->conf->get('generationPath'),
            [
                'accept'        => ['application/json'],
                'timeout'       => 2,
            ],
            $this->conf->get('accessToken')
        );
        $phpUniterIntegration = new PhpUniterIntegration($generateClient, $generateRequest);
        $placer = new Placer(new UnitTestRepository($this->conf->get('projectDirectory')));
        $keyGenerator = new RandomMaker();
        $pathCorrector = new PathCorrector();
        $namespaceGenerator = new NamespaceGenerator($this->conf->get('baseNamespace'), $this->conf->get('unitTestsDirectory'), $pathCorrector);
        $this->phpUnitService = new PhpUnitService($phpUniterIntegration, $placer, $keyGenerator, $namespaceGenerator);
        $this->preprocessor = new Preprocessor($this->conf->get('preprocess'));
        $this->obfuscatorFabric = new ObfuscatorFabric();

        $registerRequest = new RegisterRequest(
            'POST',
            $this->conf::get('baseUrl').$this->conf::get('registrationPath'),
            [
                'accept'        => ['application/json'],
                'timeout'       => 2,
            ]
        );

        $registration = new PhpUniterRegistration($generateClient, $registerRequest);
        $this->registerService = new PhpUnitUserRegisterService($registration);
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
                $this->phpUnitTest = $this->phpUnitService->process($localFile, $this->obfuscatorFabric);
                $this->report->info('Generated test was written to '.$this->phpUnitTest->getPathToTest());
            } catch (GuzzleException $e) {
                $this->report->error($e->getMessage());

                return 1;
            }
        } catch (\Throwable $e) {
            $this->report->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Execute the console command.
     */
    public function register(string $email, string $password, ?ValidatorInterface $validator = null): ?int
    {
        try {
            $validator = $validator ?? new Validator();
            $validator->setData(['email'    => $email, 'password' => $password]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            if ($this->registerService->process($email, $password)) {
                $this->report->info('User registered. Access token in your email. Put it in .env file - PHP_UNITER_ACCESS_TOKEN');
            }
        } catch (ValidationException $e) {
            $this->report->error("Command Validation Error: \n".$this->listMessages($e->errors()));

            return 1;
        } catch (GuzzleException $e) {
            $this->report->error($e->getMessage());

            return 1;
        } catch (\Throwable $e) {
            $this->report->error($e->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * @param string[][] $messages
     */
    public function listMessages(array $messages): string
    {
        $res = '';
        foreach ($messages as $key=>$item) {
            $res .= $key.' => '.implode(' ', array_values($item))."\n";
        }

        return $res;
    }

    public function getPhpUnitTest(): PhpUnitTest
    {
        return $this->phpUnitTest;
    }
}
