<?php

namespace PhpUniter\External;

class Validator
{

    private array $errors = [];
    private array $data = [];
    private array $rules = [];

    public static function make(array $data, array $rules): Validator
    {
        $validator = new Validator();
        $validator->setData($data);
        $validator->setRules($rules);
        return $validator;
    }

    public function fails(): bool
    {
        return false;
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

    /**
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * @param array $rules
     */
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }

}