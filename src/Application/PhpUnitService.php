<?php

namespace Uniter1\UniterRequester\Application;

use Uniter1\UniterRequester\Application\File\Entity\LocalFile;
use Uniter1\UniterRequester\Application\File\Exception\ObfucsatorNull;
use Uniter1\UniterRequester\Application\Generation\Exception\TestNotCreated;
use Uniter1\UniterRequester\Application\Generation\NamespaceGenerator;
use Uniter1\UniterRequester\Application\Generation\UseGenerator;
use Uniter1\UniterRequester\Application\Obfuscator\Entity\ObfuscatedClass;
use Uniter1\UniterRequester\Application\Obfuscator\KeyGenerator\ObfuscateNameMaker;
use Uniter1\UniterRequester\Application\Obfuscator\ObfuscatorFabric;
use Uniter1\UniterRequester\Application\PhpUniter\Entity\PhpUnitTest;
use Uniter1\UniterRequester\Application\PhpUniter\Exception\GeneratedTestEmpty;
use Uniter1\UniterRequester\Application\PhpUniter\Exception\LocalFileEmpty;
use Uniter1\UniterRequester\Infrastructure\Integrations\PhpUniterIntegration;

class PhpUnitService
{
    public Placer $testPlacer;
    private PhpUniterIntegration $integration;
    private ObfuscateNameMaker $keyGenerator;
    private NamespaceGenerator $namespaceGenerator;
    private UseGenerator $useGenerator;

    private bool $toObfuscate;
    private bool $inspectorMode;
    private bool $useDependent;

    public function __construct(
        PhpUniterIntegration $phpUniterIntegration,
        Placer $testPlacer,
        ObfuscateNameMaker $keyGenerator,
        NamespaceGenerator $namespaceGenerator,
        UseGenerator $useGenerator,
        array $options = []
    ) {
        $this->integration = $phpUniterIntegration;
        $this->testPlacer = $testPlacer;
        $this->keyGenerator = $keyGenerator;
        $this->toObfuscate = $options['toObfuscate'] ?? true;
        $this->inspectorMode = $options['inspectorMode'] ?? true;
        $this->useDependent = $options['useDependent'] ?? true;
        $this->namespaceGenerator = $namespaceGenerator;
        $this->useGenerator = $useGenerator;
    }

    /**
     * @throws File\Exception\DirectoryPathWrong
     * @throws File\Exception\FileNotAccessed
     * @throws TestNotCreated
     * @throws \Uniter1\UniterRequester\Application\Obfuscator\Exception\ObfuscationFailed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Uniter1\UniterRequester\Infrastructure\Exception\PhpUnitTestInaccessible
     * @throws GeneratedTestEmpty
     * @throws ObfucsatorNull
     * @throws LocalFileEmpty
     * @throws \Uniter1\UniterRequester\Infrastructure\Exception\PhpUnitRegistrationInaccessible
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
            $phpUnitTest = $this->integration->generatePhpUnitTest($obfuscatedSourceFile, $this->inspectorMode, $this->useDependent);
            $testObfuscatedGenerated = $phpUnitTest->getObfuscatedUnitTest();

            $deObfuscated = $obfuscator->deObfuscate($testObfuscatedGenerated);
            $phpUnitTest->setFinalUnitTest($deObfuscated);
        } else {
            $phpUnitTest = $this->integration->generatePhpUnitTest($classFile, $this->inspectorMode, $this->useDependent);
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
