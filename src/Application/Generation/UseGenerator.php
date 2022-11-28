<?php

namespace PhpUniter\PhpUniterRequester\Application\Generation;

class UseGenerator
{
    private $use;

    public function __construct($use)
    {
        $this->use = $use;
    }

    public function getUseHelper(string $testText): string
    {
        if (false === strpos($testText, 'PhpUnitTestHelper')) {
            return '';
        }

        return 'use '.$this->getUse().';';
    }

    /**
     * @return mixed
     */
    public function getUse()
    {
        return $this->use;
    }

    public function addUse(string $use, string $code): string
    {
        $replace = '<?php'."\n".$use."\n";

        return str_replace("<?php\n", $replace, $code);
    }
}
