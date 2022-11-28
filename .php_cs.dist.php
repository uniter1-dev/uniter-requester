<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude([
        'public',
        'resources',
        'storage',
        'vendor'
    ])
    ->in(__DIR__)
;

$config = new PhpCsFixer\Config();

return $config->setRules([
        '@Symfony' => true,
        'binary_operator_spaces' => ['operators' => ['=>' => 'align']]
    ])
    ->setFinder($finder)
;
