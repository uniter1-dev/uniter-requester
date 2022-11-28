<?php

namespace PhpUniter\PhpUniterRequester\Application;

use PhpUniter\PhpUniterRequester\Infrastructure\Exception\PhpUnitRegistrationInaccessible;
use PhpUniter\PhpUniterRequester\Infrastructure\Integrations\PhpUniterRegistration;

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
     * @throws \PhpUniter\PhpUniterRequester\Infrastructure\Exception\PhpUnitTestInaccessible
     */
    public function process(string $email, string $password): bool
    {
        return $this->registration->registerPhpUnitUser($email, $password);
    }
}
