<?php

$header = <<<'HEADER'
(c) 2023 Michael Joyce <mjoyce@sfu.ca>
This source file is subject to the GPL v2, bundled
with this source code in the file LICENSE.
HEADER;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->notPath(__FILE__)
;

// see https://cs.symfony.com/doc/rules/index.html
$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setCacheFile('.php_cs.cache')
    ->setFinder($finder)
    ->setRules([
        '@DoctrineAnnotation' => true,

        '@PhpCsFixer' => true,
        '@PSR2' => true,

        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,

        // Alias
        'array_push' => true,
        'backtick_to_shell_exec' => true,
        'ereg_to_preg' => true,
        'mb_str_functions' => true,
        'modernize_strpos' => true,
        'no_alias_functions' => ['sets' => ['@all']],
        'no_alias_language_construct_call' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'pow_to_exponentiation' => true,
        'random_api_migration' => [
            'replacements' => [
                'mt_rand' => 'random_int',
                'rand' => 'random_int',
            ]],
        'set_type_to_cast' => true,

        // Array Notation
        'array_syntax' => ['syntax' => 'short'],
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_trailing_comma_in_singleline' => true,
        'no_whitespace_before_comma_in_array' => ['after_heredoc' => true],
        'normalize_index_brace' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => true,

        // Basic
        'braces' => [
            'allow_single_line_anonymous_class_with_empty_body' => true,
            'allow_single_line_closure' => true,
            'position_after_functions_and_oop_constructs' => 'same',
            'position_after_control_structures' => 'same',
            'position_after_anonymous_constructs' => 'same',
        ],
        'curly_braces_position' => [
            'control_structures_opening_brace' => 'same_line',
            'functions_opening_brace' => 'same_line',
            'anonymous_functions_opening_brace' => 'same_line',
            'classes_opening_brace' => 'same_line',
            'anonymous_classes_opening_brace' => 'same_line',
            'allow_single_line_empty_anonymous_classes' => true,
            'allow_single_line_anonymous_functions' => true,
        ],
        'encoding' => true,
        'no_multiple_statements_per_line' => true,
        'non_printable_character' => [
            'use_escape_sequences_in_strings' => true,
        ],
        'octal_notation' => true,
        'psr_autoloading' => true,

        // Casing
        'class_reference_name_casing' => true,
        'constant_case' => true,
        'integer_literal_case' => true,
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'native_function_casing' => true,
        'native_function_type_declaration_casing' => true,

        // Cast Notation
        'cast_spaces' => ['space' => 'single'],
        'lowercase_cast' => true,
        'modernize_types_casting' => true,
        'no_short_bool_cast' => true,
        'no_unset_cast' => true,
        'short_scalar_cast' => true,

        // Class Notation
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one',
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'one',
                'case' => 'one',
            ],
        ],
        'class_definition' => [
            'multi_line_extends_each_single_line' => false,
            'single_item_single_line' => false,
            'single_line' => true,
            'space_before_parenthesis' => false,
            'inline_constructor_arguments' => true,
        ],
        'final_class' => false,
        'final_internal_class' => false,
        'final_public_method_for_abstract_class' => false,
        'no_blank_lines_after_class_opening' => false,
        'no_null_property_initialization' => true,
        'no_php4_constructor' => true,
        'no_unneeded_final_method' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant',
                'constant_public',
                'constant_protected',
                'constant_private',
                'case',
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
            ],
        ],
        'ordered_interfaces' => ['order' => 'alpha', 'direction' => 'ascend'],
        'ordered_traits' => true,
        'protected_to_private' => true,
        'self_accessor' => true,
        'self_static_accessor' => true,
        'single_class_element_per_statement' => ['elements' => ['const', 'property']],
        'single_trait_insert_per_statement' => true,
        'visibility_required' => ['elements' => ['property', 'method', 'const']],

        // Class Usage
        'date_time_immutable' => true,

        // Comment
        'comment_to_phpdoc' => ['ignored_tags' => ['todo', 'see']],
        'header_comment' => [
            'header' => $header,
        ],
        'multiline_comment_opening_closing' => true,
        'no_empty_comment' => true,
        'no_trailing_whitespace_in_comment' => true,
        'single_line_comment_style' => true,

        // Constant Notation
        'native_constant_invocation' => false,

        // Control Structure
        'control_structure_braces' => true,
        'control_structure_continuation_position' => ['position' => 'same_line'],
        'elseif' => false,
        'empty_loop_body' => ['style' => 'braces'],
        'empty_loop_condition' => ['style' => 'while'],
        'include' => true,
        'no_alternative_syntax' => true,
        'no_break_comment' => true,
        'no_superfluous_elseif' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_curly_braces' => ['namespaces' => true],
        'no_useless_else' => true,
        'simplified_if_return' => false,
        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,
        'switch_continue_to_break' => true,
        'trailing_comma_in_multiline' => [
            'after_heredoc' => true,
            'elements' => ['arrays', 'arguments'],
        ],
        'yoda_style' => true,

        // Doctrine Annotation
        'doctrine_annotation_array_assignment' => true,
        'doctrine_annotation_braces' => true,
        'doctrine_annotation_indentation' => true,
        'doctrine_annotation_spaces' => true,

        // Function Notation
        'combine_nested_dirname' => true,
        'date_time_create_from_format_call' => true,
        'fopen_flag_order' => true,
        'fopen_flags' => ['b_mode' => false],
        'function_declaration' => ['closure_function_spacing' => 'none'],
        'function_typehint_space' => true,
        'implode_call' => true,
        'lambda_not_used_import' => true,
        'method_argument_space' => [
            'keep_multiple_spaces_after_comma' => false,
            'on_multiline' => 'ensure_fully_multiline',
            'after_heredoc' => false,
        ],
        'native_function_invocation' => false,
        'no_spaces_after_function_name' => true,
        'no_trailing_comma_in_singleline_function_call' => true,
        'no_unreachable_default_argument_value' => true,
        'no_useless_sprintf' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'phpdoc_to_param_type' => true,
        'phpdoc_to_property_type' => true,
        'phpdoc_to_return_type' => true,
        'regular_callable_call' => true,
        'return_type_declaration' => ['space_before' => 'one'],
        'single_line_throw' => true,
        'static_lambda' => false,
        'use_arrow_functions' => true,
        'void_return' => true,

        // Import
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'group_import' => false,
        'no_leading_import_slash' => true,
        'no_unneeded_import_alias' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
            'imports_order' => ['const', 'function', 'class'],
        ],
        'single_import_per_statement' => true,
        'single_line_after_imports' => true,

        // Language Construct
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'declare_equal_normalize' => true,
        'dir_constant' => true,
        'error_suppression' => true,
        'explicit_indirect_variable' => true,
        'function_to_constant' => true,
        'get_class_to_class_keyword' => true,
        'is_null' => true,
        'no_unset_on_property' => true,
        'single_space_after_construct' => true,

        // List Notation
        'list_syntax' => ['syntax' => 'long'],

        // Namespace Notation
        'blank_line_after_namespace' => true,
        'clean_namespace' => true,
        'no_blank_lines_before_namespace' => false,
        'no_leading_namespace_whitespace' => true,
        'single_blank_line_before_namespace' => true,

        // Naming
        'no_homoglyph_names' => true,

        // Operators
        'assign_null_coalescing_to_coalesce_equal' => true,
        'binary_operator_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => ['style' => 'post'],
        'logical_operators' => true,
        'new_with_braces' => true,
        'no_space_around_double_colon' => true,
        'no_useless_concat_operator' => ['juggle_simple_strings' => true],
        'not_operator_with_space' => true,
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => false,
        'operator_linebreak' => ['only_booleans' => false, 'position' => 'beginning'],
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_elvis_operator' => true,
        'ternary_to_null_coalescing' => true,
        'unary_operator_spaces' => true,

        // PHP Tag
        'blank_line_after_opening_tag' => true,
        'echo_tag_syntax' => [
            'format' => 'long',
            'long_function' => 'echo',
            'shorten_simple_statements_only' => false,
        ],
        'full_opening_tag' => true,
        'no_closing_tag' => true,

        // PHP Unit
        'php_unit_construct' => true,
        'php_unit_data_provider_static' => true,
        'php_unit_dedicate_assert' => ['target' => '5.6'],
        'php_unit_dedicate_assert_internal_type' => ['target' => '7.5'],
        'php_unit_expectation' => ['target' => '5.6'],
        'php_unit_fqcn_annotation' => true,
        'php_unit_internal_class' => false,
        'php_unit_method_casing' => true,
        'php_unit_mock' => ['target' => '5.5'],
        'php_unit_mock_short_will_return' => true,
        'php_unit_namespaced' => ['target' => '6.0'],
        'php_unit_no_expectation_annotation' => ['target' => '4.3'],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_size_class' => false,
        'php_unit_strict' => true,
        'php_unit_test_annotation' => ['style' => 'annotation'],
        'php_unit_test_case_static_method_calls' => ['call_type' => 'this'],
        'php_unit_test_class_requires_covers' => false,

        // PHPDoc
        'align_multiline_comment' => [
            'comment_type' => 'all_multiline',
        ],
        'general_phpdoc_annotation_remove' => ['annotations' => ['author']],
        'general_phpdoc_tag_rename' => [
            'replacements' => [
                'inheritDocs' => 'inheritDoc',
            ],
            'fix_annotation' => false,
        ],
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true],
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_annotation_without_dot' => false,
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_line_span' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_alias_tag' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_no_package' => true,
        'phpdoc_no_useless_inheritdoc' => true,
        'phpdoc_order_by_value' => [
            'annotations' => [
                'dataProvider',
                'group',
                'method',
                'property',
                'throws',
                'uses',
            ],
        ],
        'phpdoc_order' => true,
        'phpdoc_return_self_reference' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_tag_casing' => true,
        'phpdoc_tag_type' => ['tags' => ['inheritDoc' => 'inline']],
        'phpdoc_to_comment' => ['ignored_tags' => ['todo']],
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_types_order' => [
            'sort_algorithm' => 'alpha',
            'null_adjustment' => 'always_first',
        ],
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => true,

        // Return Notation
        'no_useless_return' => true,
        'return_assignment' => true,
        'simplified_null_return' => true,

        // Semicolon
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_empty_statement' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'semicolon_after_instruction' => true,
        'space_after_semicolon' => ['remove_in_empty_for_expressions' => false],

        // Strict
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,

        // String Notation
        'escape_implicit_backslashes' => true,
        'explicit_string_variable' => true,
        'heredoc_to_nowdoc' => true,
        'no_binary_string' => true,
        'no_trailing_whitespace_in_string' => true,
        'simple_to_complex_string_variable' => true,
        'single_quote' => true,
        'string_length_to_empty' => true,
        'string_line_ending' => true,

        // Whitespace
        'array_indentation' => true,
        'blank_line_before_statement' => [
            'statements' => [
                'case',
                'continue',
                'declare',
                'do',
                'exit',
                'goto',
                'include',
                'include_once',
                'phpdoc',
                'require',
                'require_once',
                'return',
                'switch',
                'throw',
                'try',
                'yield',
                'yield_from',
            ],
        ],
        'blank_line_between_import_groups' => true,
        'compact_nullable_typehint' => true,
        'heredoc_indentation' => true,
        'indentation_type' => true,
        'line_ending' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => true,
        'no_spaces_around_offset' => true,
        'no_spaces_inside_parenthesis' => true,
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'statement_indentation' => true,
        'types_spaces' => [
            'space' => 'single',
            'space_multiple_catch' => 'single',
        ],
    ]);
