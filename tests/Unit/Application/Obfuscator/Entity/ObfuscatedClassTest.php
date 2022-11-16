<?php

namespace PhpUniter\Requester\Tests\Unit\Application\Obfuscator\Entity;

use PHPUnit\Framework\TestCase;
use PhpUniter\Requester\Application\File\Entity\LocalFile;
use PhpUniter\Requester\Application\Obfuscator\Entity\ObfuscatedClass;
use PhpUniter\Requester\Application\Obfuscator\KeyGenerator\StableMaker;
use PhpUniter\Requester\Application\Obfuscator\Obfuscator;

class ObfuscatedClassTest extends TestCase
{
    /**
     * @dataProvider getObfuscatedFileBody
     */
    public function testGetObfuscated($input, $expected)
    {
        $localFile = new LocalFile('', $input);
        $obfuscatedClassObject = new ObfuscatedClass(
            $localFile,
            new StableMaker(),
            new Obfuscator()
        );
        $obfuscated = $obfuscatedClassObject->getObfuscatedFileBody();
        $this->assertEquals(trim($expected), trim($obfuscated));

        $deObfuscated = $obfuscatedClassObject->deObfuscate($obfuscated);
        $this->assertEquals($input, $deObfuscated);
    }

    public function getObfuscatedFileBody()
    {
        return [
            [
                file_get_contents(__DIR__.'/Fixtures/SourceClass.php.input'),
                file_get_contents(__DIR__.'/Fixtures/ObfuscatedClass.php.expected'),
            ],
        ];
    }
}
