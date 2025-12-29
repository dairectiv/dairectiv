<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Rules;

use Dairectiv\SharedKernel\Domain\Object\Exception\EntityNotFoundException;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<ClassMethod>
 */
final readonly class RepositoryMethodRule implements Rule
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return ClassMethod::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof ClassMethod) {
            return [];
        }

        if (!$scope->isInClass()) {
            return [];
        }

        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return [];
        }

        // Only check Repository interfaces in Domain\Repository namespace
        if (!$classReflection->isInterface()) {
            return [];
        }

        $className = $classReflection->getName();
        if (!str_contains($className, 'Domain\\Repository\\') || !str_ends_with($className, 'Repository')) {
            return [];
        }

        $methodName = $node->name->toString();
        $errors = [];

        if (str_starts_with($methodName, 'get')) {
            $errors = $this->validateGetMethod($node, $scope, $methodName);
        } elseif (str_starts_with($methodName, 'find')) {
            $errors = $this->validateFindMethod($node, $methodName);
        }

        return $errors;
    }

    /**
     * @return list<\PHPStan\Rules\RuleError>
     */
    private function validateGetMethod(ClassMethod $node, Scope $scope, string $methodName): array
    {
        $errors = [];

        // Check return type is non-nullable
        $returnType = $node->getReturnType();
        if ($returnType === null) {
            $errors[] = RuleErrorBuilder::message(
                \sprintf('Repository method "%s" must have a return type.', $methodName),
            )->build();
        } elseif ($returnType instanceof Node\NullableType) {
            $errors[] = RuleErrorBuilder::message(
                \sprintf('Repository method "%s" must return a non-nullable type.', $methodName),
            )->build();
        }

        // Check for @throws annotation with EntityNotFoundException subclass
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            $errors[] = RuleErrorBuilder::message(
                \sprintf('Repository method "%s" must have a @throws annotation with an exception extending EntityNotFoundException.', $methodName),
            )->build();
        } else {
            $docText = $docComment->getText();
            if (!preg_match('/@throws\s+([^\s]+)/', $docText, $matches)) {
                $errors[] = RuleErrorBuilder::message(
                    \sprintf('Repository method "%s" must have a @throws annotation with an exception extending EntityNotFoundException.', $methodName),
                )->build();
            } else {
                $exceptionClass = $matches[1];

                // Resolve the exception class name
                $resolvedExceptionClass = $this->resolveClassName($exceptionClass, $scope);

                if ($resolvedExceptionClass !== null && $this->reflectionProvider->hasClass($resolvedExceptionClass)) {
                    $exceptionReflection = $this->reflectionProvider->getClass($resolvedExceptionClass);

                    if (!$exceptionReflection->isSubclassOf(EntityNotFoundException::class)) {
                        $errors[] = RuleErrorBuilder::message(
                            \sprintf(
                                'Repository method "%s" @throws annotation must reference an exception extending EntityNotFoundException, got "%s".',
                                $methodName,
                                $resolvedExceptionClass,
                            ),
                        )->build();
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @return list<\PHPStan\Rules\RuleError>
     */
    private function validateFindMethod(ClassMethod $node, string $methodName): array
    {
        $errors = [];

        $returnType = $node->getReturnType();
        if ($returnType === null) {
            $errors[] = RuleErrorBuilder::message(
                \sprintf('Repository method "%s" must have a return type.', $methodName),
            )->build();
        } elseif (!$returnType instanceof Node\NullableType) {
            $errors[] = RuleErrorBuilder::message(
                \sprintf('Repository method "%s" must return a nullable type.', $methodName),
            )->build();
        }

        return $errors;
    }

    private function resolveClassName(string $className, Scope $scope): ?string
    {
        // If it's already a fully qualified class name
        if (str_starts_with($className, '\\')) {
            return ltrim($className, '\\');
        }

        // Try to resolve using the current namespace context
        $classReflection = $scope->getClassReflection();
        if ($classReflection === null) {
            return null;
        }

        // Get the namespace from the current class
        $namespace = $classReflection->getName();
        $parts = explode('\\', $namespace);
        array_pop($parts);
        $currentNamespace = implode('\\', $parts);

        // Try the class in the current namespace first
        $fullyQualified = $currentNamespace . '\\' . $className;
        if ($this->reflectionProvider->hasClass($fullyQualified)) {
            return $fullyQualified;
        }

        // Try as-is (might be imported via use statement)
        if ($this->reflectionProvider->hasClass($className)) {
            return $className;
        }

        return null;
    }
}
