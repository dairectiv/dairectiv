<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\Alias\ModernizeStrposFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\Basic\BracesFixer;
use PhpCsFixer\Fixer\Basic\NonPrintableCharacterFixer;
use PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer;
use PhpCsFixer\Fixer\ClassNotation\NoUnneededFinalMethodFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\Fixer\ConstantNotation\NativeConstantInvocationFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\FunctionNotation\FopenFlagsFixer;
use PhpCsFixer\Fixer\FunctionNotation\NullableTypeDeclarationForDefaultNullValueFixer;
use PhpCsFixer\Fixer\FunctionNotation\VoidReturnFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\OperatorLinebreakFixer;
use PhpCsFixer\Fixer\Phpdoc\AlignMultilineCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocOrderFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSeparationFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSummaryFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimConsecutiveBlankLineSeparationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitTestCaseStaticMethodCallsFixer;
use PhpCsFixer\Fixer\ReturnNotation\NoUselessReturnFixer;
use PhpCsFixer\Fixer\Semicolon\MultilineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\Strict\StrictParamFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\BlankLineBetweenImportGroupsFixer;
use PhpCsFixer\Fixer\Whitespace\CompactNullableTypehintFixer;
use PhpCsFixer\Fixer\Whitespace\MethodChainingIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use PhpCsFixer\FixerFactory;
use PhpCsFixer\RuleSet\RuleSet;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->parallel(maxNumberOfProcess: 8);
    $ecsConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/config',
        __DIR__.'/migrations',
        __DIR__.'/rector.php',
        __DIR__.'/ecs.php',
    ]);

    $ecsConfig->cacheDirectory(__DIR__.'/var/cache/.ecs.cache');

    // Specific ruleset
    // @see https://github.com/symplify/symplify/pull/4070#issuecomment-1135896943
    $ruleSet = new RuleSet([
        '@DoctrineAnnotation'       => true,
        '@PHP71Migration'           => true,
        '@PHP71Migration:risky'     => true,
        '@PHPUnit60Migration:risky' => true,
        '@Symfony'                  => true,
        '@Symfony:risky'            => true,
    ]);

    $fixerFactory = new FixerFactory();
    $fixerFactory->registerBuiltInFixers();
    $fixerFactory->useRuleSet($ruleSet);

    foreach ($fixerFactory->getFixers() as $fixer) {
        $ruleClass = $fixer::class;
        if ($fixer instanceof ConfigurableFixerInterface) {
            $configuration = $ruleSet->getRuleConfiguration($fixer->getName());
            if (null !== $configuration) {
                $ecsConfig->ruleWithConfiguration($ruleClass, $configuration);
                continue;
            }
        }

        $ecsConfig->rule($ruleClass);
    }

    // ----------------------------------
    // Skip directories
    // ----------------------------------
    $ecsConfig->skip([
        __DIR__.'/config/bundles.php',
    ]);

    // ----------------------------------
    // External tools
    // ----------------------------------

    // ----------------------------------
    // Alias
    // ----------------------------------
    $ecsConfig->skip([
        ModernizeStrposFixer::class, // todo check if we keep or not
    ]);

    // ----------------------------------
    // Array Notation
    // ----------------------------------
    $ecsConfig->skip([
        NoWhitespaceBeforeCommaInArrayFixer::class,
    ]);
    $ecsConfig->rulesWithConfiguration([
        ArraySyntaxFixer::class => [
            'syntax' => 'short',
        ],
    ]);

    // ----------------------------------
    // Basic
    // ----------------------------------
    $ecsConfig->skip([
        NonPrintableCharacterFixer::class,
    ]);
    $ecsConfig->rulesWithConfiguration([
        BracesFixer::class => [
            'allow_single_line_closure' => true,
        ],
    ]);

    // ----------------------------------
    // Class Notation
    // ----------------------------------
    $ecsConfig->skip([
        NoNullPropertyInitializationFixer::class,
        SelfAccessorFixer::class,
    ]);

    // ----------------------------------
    // Control structure
    // ----------------------------------
    $ecsConfig->rules([
        NoUselessElseFixer::class,
    ]);

    $ecsConfig->ruleWithConfiguration(
        TrailingCommaInMultilineFixer::class,
        [
            'after_heredoc' => true,
            'elements'      => ['arguments', 'arrays', 'match', 'parameters'],
        ],
    );

    // ----------------------------------
    // Function Notation
    // ----------------------------------
    $ecsConfig->skip([
        VoidReturnFixer::class,
        NullableTypeDeclarationForDefaultNullValueFixer::class, // todo check if we keep or not
    ]);
    $ecsConfig->rules([
        NoUnneededFinalMethodFixer::class,
        NativeConstantInvocationFixer::class,
    ]);
    $ecsConfig->rulesWithConfiguration([
        FopenFlagsFixer::class => [
            'b_mode' => true,
        ],
    ]);

    // ----------------------------------
    // Import
    // ----------------------------------
    $ecsConfig->rulesWithConfiguration([
        OrderedImportsFixer::class => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha',
        ],
        GlobalNamespaceImportFixer::class => [
            'import_classes'   => null, // todo check if we cannot put `true`
            'import_constants' => null,
            'import_functions' => null,
        ],
    ]);

    // ----------------------------------
    // Operator
    // ----------------------------------
    $ecsConfig->skip([
        OperatorLinebreakFixer::class, // todo check if we keep or not
    ]);

    // ----------------------------------
    // PHPDoc
    // ----------------------------------
    $ecsConfig->skip([
        PhpdocSeparationFixer::class,
        PhpdocSummaryFixer::class,
        PhpdocToCommentFixer::class,
        NoSuperfluousPhpdocTagsFixer::class, // todo check if we keep or not
    ]);
    $ecsConfig->rules([
        PhpdocTrimConsecutiveBlankLineSeparationFixer::class,
    ]);
    $ecsConfig->rulesWithConfiguration([
        AlignMultilineCommentFixer::class => [
            'comment_type' => 'phpdocs_only',
        ],
        PhpdocOrderFixer::class => [
            'order' => ['param', 'throws', 'return'],
        ],
        PhpdocAlignFixer::class => [
            'align' => 'left',
        ],
    ]);

    // ----------------------------------
    // PHPUnit
    // ----------------------------------
    $ecsConfig->rulesWithConfiguration([
        PhpUnitMethodCasingFixer::class => [
            'case' => 'camel_case',
        ],
        PhpUnitTestCaseStaticMethodCallsFixer::class => [
            'call_type' => 'self',
        ],
    ]);
    $ecsConfig->rulesWithConfiguration([
        ConcatSpaceFixer::class => [
            'spacing' => 'none',
        ],
        BinaryOperatorSpacesFixer::class => [
            'default'   => 'single_space',
            'operators' => [
                '=>' => 'align',
            ],
        ],
    ]);

    // ----------------------------------
    // ReturnNotation
    // ----------------------------------
    $ecsConfig->rules([
        NoUselessReturnFixer::class,
    ]);

    // ----------------------------------
    // Semicolon
    // ----------------------------------
    $ecsConfig->rulesWithConfiguration([
        MultilineWhitespaceBeforeSemicolonsFixer::class => [
            'strategy' => 'new_line_for_chained_calls',
        ],
    ]);

    // ----------------------------------
    // Strict
    // ----------------------------------
    $ecsConfig->rules([
        StrictComparisonFixer::class,
        StrictParamFixer::class,
        DeclareStrictTypesFixer::class,
    ]);

    // ----------------------------------
    // Whitespace
    // ----------------------------------
    $ecsConfig->skip([
        BlankLineBetweenImportGroupsFixer::class,
        TypeDeclarationSpacesFixer::class, // todo check if we keep or not
    ]);
    $ecsConfig->rules([
        ArrayIndentationFixer::class,
        CompactNullableTypehintFixer::class,
        MethodChainingIndentationFixer::class,
    ]);
    $ecsConfig->rulesWithConfiguration([
        NoExtraBlankLinesFixer::class => [
            'tokens' => [
                'break',
                'continue',
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        TypesSpacesFixer::class => [
            'space' => 'single',
        ],
    ]);
};
