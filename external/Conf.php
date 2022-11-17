<?php

namespace PhpUniter\External;

class Conf
{
    private static array $settings = [
        'accessToken'         => 'DXtuFo4riyIAwf5mBDg1ri7x02ki04d1QANrsuGK',
        'baseUrl'             => 'http://uniter1.loc',
        'projectDirectory'    => '/home/sergey/http/requester',
        'preprocess'          => false,
        'obfuscate'           => false,
        'unitTestBaseClass'   => 'PHPUnit\Framework\TestCase',
        'unitTestsDirectory'  => 'tests/Resulted',
        'baseNamespace'       => 'Tests\Unit',
        'basePath'            => '/home/sergey/http/requester',
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