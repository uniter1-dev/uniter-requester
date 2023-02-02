<?php

namespace Uniter1\UniterRequester\Application;

use GuzzleHttp\Exception\GuzzleException;
use Uniter1\UniterRequester\Application\File\Entity\LocalFile;
use Uniter1\UniterRequester\Application\File\Exception\ObfucsatorNull;
use Uniter1\UniterRequester\Application\Generation\Exception\TestNotCreated;
use Uniter1\UniterRequester\Application\Generation\NamespaceGenerator;
use Uniter1\UniterRequester\Application\Generation\UseGenerator;
use Uniter1\UniterRequester\Application\Obfuscator\Entity\ObfuscatedClass;
use Uniter1\UniterRequester\Application\Obfuscator\Exception\ObfuscationFailed;
use Uniter1\UniterRequester\Application\Obfuscator\KeyGenerator\ObfuscateNameMaker;
use Uniter1\UniterRequester\Application\Obfuscator\ObfuscatorFabric;
use Uniter1\UniterRequester\Application\PhpParser\RequesterParser;
use Uniter1\UniterRequester\Application\PhpUniter\Entity\PhpUnitTest;
use Uniter1\UniterRequester\Application\PhpUniter\Exception\GeneratedTestEmpty;
use Uniter1\UniterRequester\Application\PhpUniter\Exception\LocalFileEmpty;
use Uniter1\UniterRequester\Infrastructure\Exception\FileNotFound;
use Uniter1\UniterRequester\Infrastructure\Exception\MethodReplaceFail;
use Uniter1\UniterRequester\Infrastructure\Exception\PhpUnitRegistrationInaccessible;
use Uniter1\UniterRequester\Infrastructure\Exception\PhpUnitTestInaccessible;
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
     * @throws ObfuscationFailed
     * @throws GuzzleException
     * @throws PhpUnitTestInaccessible
     * @throws GeneratedTestEmpty
     * @throws ObfucsatorNull
     * @throws LocalFileEmpty
     * @throws MethodReplaceFail
     * @throws FileNotFound
     * @throws PhpUnitRegistrationInaccessible
     * @throws \Exception
     */
    public function process(LocalFile $classFile, ObfuscatorFabric $obfuscatorFabric, string $overwriteOneMethod): PhpUnitTest
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
            $phpUnitTest = $this->integration->generatePhpUnitTest($obfuscatedSourceFile, $this->inspectorMode, $this->useDependent, $overwriteOneMethod);
            if (!$overwriteOneMethod) {
                $testObfuscatedGenerated = $phpUnitTest->getObfuscatedUnitTest();

                $deObfuscated = $obfuscator->deObfuscate($testObfuscatedGenerated);
                $phpUnitTest->setFinalUnitTest($deObfuscated);
            } else {
                $obfuscatedMethods = $phpUnitTest->getTestMethods();
                $deObfuscatedMethods = $obfuscator->deObfuscateMethods($obfuscatedMethods);
            }
        } else {
            $phpUnitTest = $this->integration->generatePhpUnitTest($classFile, $this->inspectorMode, $this->useDependent, $overwriteOneMethod);
            $phpUnitTest->setFinalUnitTest($phpUnitTest->getObfuscatedUnitTest());
            $deObfuscatedMethods = $phpUnitTest->getTestMethods();
        }

        $className = $phpUnitTest->getClassName();
        $srcNamespace = $phpUnitTest->getNamespace();

        $relativePath = $this->namespaceGenerator->makePathToTest($srcNamespace);
        $testNamespace = $this->namespaceGenerator->makeNamespace($srcNamespace);

        if (!$overwriteOneMethod) {
            $testText = $phpUnitTest->getFinalUnitTest();
            $useHelper = $this->useGenerator->getUseHelper($testText);
            $testCode = $this->useGenerator->addUse($useHelper, $testText);
            $testCode = $this->namespaceGenerator->addNamespace($testCode, $testNamespace);
        } else {
            $testText = $this->testPlacer->getOldTest($relativePath, $className.'Test.php');
            $testCode = RequesterParser::fetch($testText, $deObfuscatedMethods, $overwriteOneMethod);

            if (empty($testCode)) {
                throw new MethodReplaceFail('Method replace fail');
            }

            $testCode = str_replace("}\n\n", "}\n", $testCode);
        }

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
