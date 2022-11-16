<?php

class Conf
{
    private static array $settings = [
        'accessToken'         => 'lJfGZsAM8lmyW7LYAKpbOn2iI5GZadWYAo083toD',
        'baseUrl'             => 'http://uniter1.loc',
        'projectDirectory'    => '/home/sergey/http/requester',
        'preprocess'          => true,
        'obfuscate'           => true,
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