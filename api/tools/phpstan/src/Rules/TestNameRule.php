<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @implements Rule<ClassMethod>
 */
class TestNameRule implements Rule
{
    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$scope->isInClass()) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if (!$classReflection->getNativeReflection()->isSubclassOf(TestCase::class)) {
            return [];
        }

        $methodName = $node->name->toString();

        if (!str_starts_with($methodName, 'test')) {
            return [];
        }

        if (!str_starts_with($methodName, 'testItShould')) {
            return [
                RuleErrorBuilder::message(\sprintf(
                    'Test method "%s" must start with "testItShould".',
                    $methodName,
                ))->identifier('test.name')->build(),
            ];
        }

        return [];
    }
}
