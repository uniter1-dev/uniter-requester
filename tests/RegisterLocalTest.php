<?php

namespace PhpUniter\Requester\Tests;

use PHPUnit\Framework\TestCase;
use PhpUniter\External\Conf;
use PhpUniter\External\Report;
use PhpUniter\Requester\Requester;

/**
 * @property Report $report
 */
class RegisterLocalTest extends TestCase
{
    use CreatesApplicationPackage;

    public array $container = [];
    private string $pathToTest;
    private string $projectRoot;
    private Conf $conf;

    public function setUp(): void
    {
        parent::setUp();
        $this->conf = new Conf();
        $this->pathToTest = (string) $this->conf::get('unitTestsDirectory');
        $this->projectRoot = $this->conf::get('basePath');
        $this->report = new Report();
    }

    public function testGenerate()
    {
        $requester = new Requester($this->conf, $this->report);
        $code = $requester->generate('src/PhpUnitTestHelper.php');

        self::assertEquals(0, $code);
    }

    public function testRegister()
    {
        $requester = new Requester($this->conf, $this->report);
        $code = $requester->register(
            'a'.uniqid().'@test.ru',
            'NewMockery0',
        );

        self::assertEquals(0, $code);
    }
}
