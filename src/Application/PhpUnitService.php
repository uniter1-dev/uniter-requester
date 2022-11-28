<?php

namespace PhpUniter\Requester\Application;

use PhpUniter\Requester\Application\File\Entity\LocalFile;
use PhpUniter\Requester\Application\File\Exception\ObfucsatorNull;
use PhpUniter\Requester\Application\Generation\Exception\TestNotCreated;
use PhpUniter\Requester\Application\Generation\NamespaceGenerator;
use PhpUniter\Requester\Application\Generation\UseGenerator;
use PhpUniter\Requester\Application\Obfuscator\Entity\ObfuscatedClass;
use PhpUniter\Requester\Application\Obfuscator\KeyGenerator\ObfuscateNameMaker;
use PhpUniter\Requester\Application\Obfuscator\ObfuscatorFabric;
use PhpUniter\Requester\Application\PhpUniter\Entity\PhpUnitTest;
use PhpUniter\Requester\Application\PhpUniter\Exception\GeneratedTestEmpty;
use PhpUniter\Requester\Application\PhpUniter\Exception\LocalFileEmpty;
use PhpUniter\Requester\Infrastructure\Integrations\PhpUniterIntegration;

class PhpUnitService
{
    public Placer $testPlacer;
    private PhpUniterIntegration $integration;
    private ObfuscateNameMaker $keyGenerator;
    private bool $toObfuscate;
    private NamespaceGenerator $namespaceGenerator;
    private UseGenerator $useGenerator;

    public function __construct(
        PhpUniterIntegration $phpUniterIntegration,
        Placer $testPlacer,
        ObfuscateNameMaker $keyGenerator,
        NamespaceGenerator $namespaceGenerator,
        UseGenerator $useGenerator,
        bool $toObfuscate = true
    ) {
        $this->integration = $phpUniterIntegration;
        $this->testPlacer = $testPlacer;
        $this->keyGenerator = $keyGenerator;
        $this->toObfuscate = $toObfuscate;
        $this->namespaceGenerator = $namespaceGenerator;
        $this->useGenerator = $useGenerator;
    }

    /**
     * @throws File\Exception\DirectoryPathWrong
     * @throws File\Exception\FileNotAccessed
     * @throws TestNotCreated
     * @throws \PhpUniter\Requester\Application\Obfuscator\Exception\ObfuscationFailed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \PhpUniter\Requester\Infrastructure\Exception\PhpUnitTestInaccessible
     * @throws GeneratedTestEmpty
     * @throws ObfucsatorNull
     * @throws LocalFileEmpty
     */
    public function process(LocalFile $classFile, ObfuscatorFabric $obfuscatorFabric): PhpUnitTest
    {
        $obfuscated = $classFile;

        if ($this->toObfuscate) {
            $obfuscator = $obfuscatorFabric->getObfuscated($obfuscated, $this->keyGenerator);

            if (is_null($obfuscator)) {
                throw new ObfucsatorNull('File is not obfuscatable');
            }

            /** @var LocalFile $obfuscatedSourceFile */
            /** @var ObfuscatedClass $obfuscator */
            $obfuscatedSourceFile = $obfuscator->makeObfuscated();
            $phpUnitTest = $this->integration->generatePhpUnitTest($obfuscatedSourceFile);
            $testObfuscatedGenerated = $phpUnitTest->getObfuscatedUnitTest();

            $deObfuscated = $obfuscator->deObfuscate($testObfuscatedGenerated);
            $phpUnitTest->setFinalUnitTest($deObfuscated);
        } else {
            $phpUnitTest = $this->integration->generatePhpUnitTest($classFile);
            $phpUnitTest->setFinalUnitTest($phpUnitTest->getObfuscatedUnitTest());
        }

        $classText = $classFile->getFileBody();
        $className = $this->findClassName($classFile);

        $srcNamespace = $this->namespaceGenerator->findNamespace($classText);
        $testNamespace = $this->namespaceGenerator->makeNamespace($srcNamespace);
        $testText = $phpUnitTest->getFinalUnitTest();
        $useHelper = $this->useGenerator->getUseHelper($testText);
        $testCode = $this->useGenerator->addUse($useHelper, $testText);
        $testCode = $this->namespaceGenerator->addNamespace($testCode, $testNamespace);
        $relativePath = $this->namespaceGenerator->makePathToTest($srcNamespace);

        $phpUnitTest->setFinalUnitTest($testCode);

        $testSize = $this->testPlacer->placeUnitTest($phpUnitTest, $relativePath, $className.'Test.php');

        if (empty($testSize)) {
            throw new GeneratedTestEmpty('Empty test written');
        }

        return $phpUnitTest;
    }

    public function findClassName(LocalFile $classFile): string
    {
        $text = $classFile->getFileBody();
        preg_match('/(?<=class\s)(\w+)/', $text, $matches);

        return $matches[0];
    }
}
