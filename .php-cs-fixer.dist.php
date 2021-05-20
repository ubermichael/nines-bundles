<?php

$header = <<<'HEADER'
(c) 2021 Michael Joyce <mjoyce@sfu.ca>
This source file is subject to the GPL v2, bundled
with this source code in the file LICENSE.
HEADER;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->notPath(__FILE__)
;

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setCacheFile('.php_cs.cache')
    ->setFinder($finder)
    ->setRules([
        '@DoctrineAnnotation' => true,

        '@PhpCsFixer' => true,
        '@PSR2'        => true,

        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,

        'align_multiline_comment' => [
            'comment_type' => 'all_multiline',
        ],
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],

        'backtick_to_shell_exec' => true,
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
//        'blank_line_before_statement' => [
//            'statements' => [
//                'declare', 'die', 'exit', 'for', 'foreach', 'return', 'try',
//            ]
//        ],
        'braces' => [
            'allow_single_line_closure' => true,
            'position_after_functions_and_oop_constructs' => 'same'
        ],

        'cast_spaces' => [
          'space' => 'single',
        ],
        'class_attributes_separation' => true,
        'class_keyword_remove' => false,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'combine_nested_dirname' => true,
        'compact_nullable_typehint' => true,
        'concat_space' => ['spacing' => 'one'],

        'date_time_immutable' => true,
        'declare_strict_types' => true,
        'dir_constant' => true,

        'elseif' => false,
        'encoding' => true,
        'ereg_to_preg' => true,
        'escape_implicit_backslashes' => true,

        'fopen_flag_order' => true,
        'fully_qualified_strict_types' => true,
        'function_to_constant' => true,

        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],

        'header_comment' => [
            'header' => $header,
        ],
        'heredoc_to_nowdoc' => true,

        'implode_call' => true,
        'increment_style' => ['style' => 'post'],
        'is_null' => true,

        'line_ending' => true,
        'list_syntax' => ['syntax' => 'long'],

        'mb_str_functions' => true,
        'modernize_types_casting' => true,

        'no_extra_blank_lines' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_php4_constructor' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'non_printable_character' => [
          'use_escape_sequences_in_strings' => true,
        ],
        'not_operator_with_space' => true,
        'nullable_type_declaration_for_default_null_value' => true,

        'ordered_class_elements' => ['order' => [
                'use_trait',
                'constant',
                'constant_public',
                'constant_protected',
                'constant_private',
                'public',
                'protected',
                'private',
                'property',
                'property_static',
                'property_public',
                'property_protected',
                'property_private',
                'property_public_static',
                'property_protected_static',
                'property_private_static',
                'construct',
                'magic',
                'destruct',
                'method',
                'method_static',
                'method_private',
                'method_public_static',
                'method_protected_static',
                'method_private_static',
                'method_protected',
                'method_public',
                'phpunit',
            ]
        ],
        'ordered_imports' => true,

        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => [
          'target' => '5.6',
        ],
        'php_unit_dedicate_assert_internal_type' => [
          'target' => '7.5',
        ],
        'php_unit_expectation' => [
          'target' => '5.6',
        ],
        'php_unit_mock' => [
          'target' => '5.5',
        ],
        'php_unit_mock_short_will_return' => true,
        'php_unit_namespaced' => [
          'target' => '6.0',
        ],
        'php_unit_no_expectation_annotation' => [
          'target' => '4.3',
        ],


        'php_unit_internal_class' => ['types' => []],
        'php_unit_test_class_requires_covers' => false,
        'php_unit_strict' => true,

        'phpdoc_add_missing_param_annotation' => [
          'only_untyped' => false
        ],
        'phpdoc_align' => [
          'align' => 'left'
        ],
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_order' => true,

        'random_api_migration' => true,
        'return_type_declaration' => [
          'space_before' => 'one'
        ],

        'self_accessor' => true,
        'self_static_accessor' => true,

        'single_import_per_statement' => true,
        'simplified_null_return' => true,
        'strict_comparison' => true,
        'strict_param' => true,

        'ternary_to_null_coalescing' => true,

        'void_return' => true,
    ]);
