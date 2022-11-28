<?php

namespace PhpUniter\External;

class Conf
{
    private static array $settings = [
        'accessToken'         => 'WLDhA4YMiZM9x8JdpLcEnAYccNgoM2ZbsMtLud5e',
        'baseUrl'             => 'http://uniter1.loc',
        'projectDirectory'    => '/home/sergey/http/requester',
        'preprocess'          => false,
        'obfuscate'           => false,
        'unitTestBaseClass'   => 'PHPUnit\Framework\TestCase',
        'unitTestsDirectory'  => 'tests/Resulted',
        'baseNamespace'       => 'PhpUniter\Requester\Tests\Resulted',
        'helperClass'         => 'PhpUniter\Requester\PhpUnitTestHelper',
        'basePath'            => '/home/sergey/http/requester',
        'registrationPath'    => '/api/v1/registration/access-token',
        'generationPath'      => '/api/v1/generator/generate',
    ];

    public static function get($key)
    {
        return self::$settings[$key];
    }

    public static function set($key, $value): void
    {
        self::$settings[$key] = $value;
    }

    /**
     * @param string[] $settings
     */
    public static function setSettings(array $settings): void
    {
        self::$settings = $settings;
    }
}
