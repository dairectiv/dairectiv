<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Symfony as RectorSymfony;
use Rector\Symfony\Set\SymfonySetList;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel(maxNumberOfProcess: 8);
    $rectorConfig->paths([
        __DIR__.'/config',
        __DIR__.'/migrations',
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);
    $rectorConfig->phpVersion(PhpVersion::PHP_84);
    $rectorConfig->symfonyContainerXml(__DIR__.'/var/cache/dev/Dairectiv_KernelDevDebugContainer.xml');
    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory(__DIR__.'/var/cache/.rector.cache');
    $rectorConfig->sets([
        SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ]);

    // PHP rules
    $rectorConfig->rules([
        Rector\Php83\Rector\ClassConst\AddTypeToConstRector::class,
        Rector\CodingStyle\Rector\FuncCall\ArraySpreadInsteadOfArrayMergeRector::class,
        Rector\Php53\Rector\Ternary\TernaryToElvisRector::class,
        Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector::class,
        Rector\Php54\Rector\Array_\LongArrayToShortArrayRector::class,
        Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector::class,
        Rector\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector::class,
        Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector::class,
        Rector\TypeDeclaration\Rector\ClassMethod\StrictStringParamConcatRector::class,
        Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector::class,
        Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector::class,
        Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector::class,
        Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector::class,
        Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector::class,
        Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector::class,
        Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector::class,
        Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector::class,
        Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector::class,
        Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector::class,
        Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector::class,
        Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector::class,
        Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector::class,
        Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector::class,
        Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector::class,
        Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector::class,
        Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector::class,
        Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector::class,
        Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector::class,
        Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector::class,
        Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector::class,
        Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector::class,
        Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector::class,
        Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector::class,
        Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector::class,
        Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector::class,
        Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector::class,
        Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector::class,
        Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector::class,
        Rector\CodingStyle\Rector\If_\NullableCompareToNullRector::class,
        Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class,
        Rector\Php82\Rector\Class_\ReadOnlyClassRector::class,
    ]);

    // Symfony rules
    $rectorConfig->rules([
        RectorSymfony\CodeQuality\Rector\ClassMethod\ParamTypeFromRouteRequiredRegexRector::class,
        RectorSymfony\CodeQuality\Rector\ClassMethod\ActionSuffixRemoverRector::class,
        RectorSymfony\CodeQuality\Rector\ClassMethod\RemoveUnusedRequestParamRector::class,
        RectorSymfony\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector::class,
        RectorSymfony\CodeQuality\Rector\BinaryOp\ResponseStatusCodeRector::class,
        RectorSymfony\Symfony42\Rector\New_\StringToArrayArgumentProcessRector::class,
        RectorSymfony\Symfony43\Rector\MethodCall\WebTestCaseAssertIsSuccessfulRector::class,
        RectorSymfony\Symfony43\Rector\MethodCall\WebTestCaseAssertResponseCodeRector::class,
    ]);
};
