<?php

$finder = PhpCsFixer\Finder::create()
    ->ignoreDotFiles(false)
    ->ignoreVCS(true)
    ->exclude([
        'runtime',
        'vendor',
        'public/assets',
        'public/dist',
    ])
    ->in([
        __DIR__ . '/config',
        __DIR__ . '/migrations',
        __DIR__ . '/public',
        __DIR__ . '/resources',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/views',
    ]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'align_single_space_minimal',
        ],
        'cast_spaces' => ['space' => 'none'],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
        ],
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'phpdoc_align' => [
            'align' => 'vertical',
        ],
        'phpdoc_to_comment' => false,
        'return_type_declaration' => ['space_before' => 'one'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'types_spaces' => ['space' => 'single'],
    ]);
