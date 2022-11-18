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
use Throwable;

class Requester
{
    public Conf $conf;
    public PhpUnitService $phpUnitService;
    public Preprocessor $preprocessor;
    public ObfuscatorFabric $obfuscatorFabric;
    public Report $report;
    public GenerateClient $generateClient;
    public PhpUniterRegistration $registration;
    public RegisterRequest $registerRequest;
    public PhpUnitUserRegisterService $registerService;
    public ?Validator $validator;
    public Placer $placer;

    /**
     * @param Conf $conf
     * @param Report $report
     */
    public function __construct(?Conf $conf = null, ?Report $report = null)
    {
        $this->conf = $conf ?? new Conf();
        $this->report = $report ?? new Report();

        $this->generateClient = new GenerateClient();

        $generateRequest = new GenerateRequest(
            'POST',
            $this->conf->get('baseUrl').$this->conf->get('generationPath'),
            [
                'accept'        => ['application/json'],
                'timeout'       => 2,
            ],
            $this->conf->get('accessToken')
        );
        $phpUniterIntegration = new PhpUniterIntegration($this->generateClient, $generateRequest);
        $this->placer = new Placer(new UnitTestRepository($this->conf->get('projectDirectory')));
        $keyGenerator = new RandomMaker();
        $pathCorrector = new PathCorrector();
        $namespaceGenerator = new NamespaceGenerator($this->conf->get('baseNamespace'), $this->conf->get('unitTestsDirectory'), $pathCorrector);
        $this->phpUnitService = new PhpUnitService($phpUniterIntegration, $this->placer, $keyGenerator, $namespaceGenerator);
        $this->preprocessor = new Preprocessor($this->conf->get('preprocess'));
        $this->obfuscatorFabric = new ObfuscatorFabric();

        $this->registerRequest = new RegisterRequest(
            'POST',
            $this->conf::get('baseUrl').$this->conf::get('registrationPath'),
            [
                'accept'        => ['application/json'],
                'timeout'       => 2,
            ]
        );

        $this->registration = new PhpUniterRegistration($this->generateClient, $this->registerRequest);
        $this->registerService = new PhpUnitUserRegisterService($this->registration);
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

    /**
     * Execute the console command.
     */
    public function register(string $email, string $password, ?ValidatorInterface $validator = null): ?int
    {
        try {
            $this->validator = $validator ?? new Validator();
            $this->validator->setData(['email'    => $email, 'password' => $password]);

            if ($this->validator->fails()) {
                throw new ValidationException($this->validator);
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
        } catch (Throwable $e) {
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
}
