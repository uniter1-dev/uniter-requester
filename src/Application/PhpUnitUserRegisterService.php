<?php

namespace PhpUniter\Requester\Application;

use PhpUniter\Requester\Infrastructure\Exception\PhpUnitRegistrationInaccessible;
use PhpUniter\Requester\Infrastructure\Integrations\PhpUniterRegistration;

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
     * @throws \PhpUniter\Requester\Infrastructure\Exception\PhpUnitTestInaccessible
     */
    public function process(string $email, string $password): bool
    {
        return $this->registration->registerPhpUnitUser($email, $password);
    }
}
