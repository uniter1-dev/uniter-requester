<?php

namespace Uniter1\UniterRequester\Application;

use Uniter1\UniterRequester\Infrastructure\Exception\PhpUnitRegistrationInaccessible;
use Uniter1\UniterRequester\Infrastructure\Integrations\PhpUniterRegistration;

class PhpUnitUserRegisterService
{
    private PhpUniterRegistration $registration;

    public function __construct(
        PhpUniterRegistration $registration
    ) {
        $this->registration = $registration;
    }

    /**
     * @throws PhpUnitRegistrationInaccessible
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Uniter1\UniterRequester\Infrastructure\Exception\PhpUnitTestInaccessible
     */
    public function process(string $email, string $password): bool
    {
        return $this->registration->registerPhpUnitUser($email, $password);
    }
}
