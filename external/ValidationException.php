<?php

namespace PhpUniter\External;

class ValidationException extends \Exception
{
    private Validator $validator;

    public function __construct(Validator $validator, string $message = 'The given data was invalid.')
    {
        parent::__construct($message);
        $this->validator = $validator;
    }

    /**
     * Get all of the validation error messages.
     *
     * @return string[][]
     */
    public function errors(): array
    {
        return $this->validator->errors();
    }
}
