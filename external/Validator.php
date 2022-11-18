<?php

namespace PhpUniter\External;

class Validator
{
    private array $errors = [];
    private array $data = [];

    private bool $fail = false;

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function passes(): bool
    {
        if (!array_key_exists('email', $this->data)) {
            $this->setError(['No email in validation data']);
            return false;
        }

        if (!array_key_exists('password', $this->data)) {
            $this->setError(['No password in validation data']);
            return false;
        }

        $email = $this->data['email'];
        $password = $this->data['password'];

        if (empty($email)) {
            $this->setError(['Empty email in validation data']);
            return false;
        }

        if (empty($password)) {
            $this->setError(['Empty password in validation data']);
            return false;
        }

        if (!self::isValidEmail($email)) {
            $this->setError(['Email is not valid']);
            return false;
        }

        return true;
    }

    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @param string[] $messages
     */
    public function setError(array $messages): void
    {
        $this->errors[] = $messages;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
