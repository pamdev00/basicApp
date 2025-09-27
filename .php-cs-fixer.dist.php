<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setFinder(
        PhpCsFixer\Finder::create()->in([
            __DIR__ . '/public',
            __DIR__ . '/src',
            __DIR__ . '/config',
            __DIR__ . '/resources',
            __DIR__ . '/tests',
            __DIR__ . '/views',
        ])
    )
    ->setRules([
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@PHP83Migration' => true,

        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const',]],
        'fully_qualified_strict_types' => true,
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],

        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => ['space' => 'none'],
        'binary_operator_spaces' => false,

        'phpdoc_to_comment' => false,
        'phpdoc_separation' => false,
        'phpdoc_types_order' => ['null_adjustment' => 'always_last'],
        'phpdoc_align' => false,

        'operator_linebreak' => false,

        'global_namespace_import' => true,
        'echo_tag_syntax' => ['format' => 'short'], // или 'long'
        'blank_line_before_statement' => false,
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],



])
    ->setRiskyAllowed(true)
    ->setCacheFile(__DIR__ . '/runtime/.php-cs-fixer.cache')
;
