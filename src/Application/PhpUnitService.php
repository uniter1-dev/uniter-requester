<?php

namespace PhpUniter\PhpUniterRequester\Application;

use PhpUniter\PhpUniterRequester\Application\File\Entity\LocalFile;
use PhpUniter\PhpUniterRequester\Application\File\Exception\ObfucsatorNull;
use PhpUniter\PhpUniterRequester\Application\Generation\Exception\TestNotCreated;
use PhpUniter\PhpUniterRequester\Application\Generation\NamespaceGenerator;
use PhpUniter\PhpUniterRequester\Application\Generation\UseGenerator;
use PhpUniter\PhpUniterRequester\Application\Obfuscator\Entity\ObfuscatedClass;
use PhpUniter\PhpUniterRequester\Application\Obfuscator\KeyGenerator\ObfuscateNameMaker;
use PhpUniter\PhpUniterRequester\Application\Obfuscator\ObfuscatorFabric;
use PhpUniter\PhpUniterRequester\Application\PhpUniter\Entity\PhpUnitTest;
use PhpUniter\PhpUniterRequester\Application\PhpUniter\Exception\GeneratedTestEmpty;
use PhpUniter\PhpUniterRequester\Application\PhpUniter\Exception\LocalFileEmpty;
use PhpUniter\PhpUniterRequester\Infrastructure\Integrations\PhpUniterIntegration;

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
     * @throws \PhpUniter\PhpUniterRequester\Application\Obfuscator\Exception\ObfuscationFailed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \PhpUniter\PhpUniterRequester\Infrastructure\Exception\PhpUnitTestInaccessible
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
