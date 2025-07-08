<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('vendor')
    ->name('*.php')
    ->notName('*.blade.php') // si Laravel
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'no_unused_imports' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_to_comment' => false,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
