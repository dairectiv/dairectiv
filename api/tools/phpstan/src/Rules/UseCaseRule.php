<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Rules;

use Dairectiv\SharedKernel\Application\Command\Command;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use Dairectiv\SharedKernel\Application\Query\Query;
use Dairectiv\SharedKernel\Application\Query\QueryHandler;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Class_>
 */
final readonly class UseCaseRule implements Rule
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
    ) {
    }

    public function getNodeType(): string
    {
        return Class_::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node instanceof Class_) {
            return [];
        }

        if ($node->name === null) {
            return [];
        }

        $className = $node->name->toString();

        if ($className !== 'Handler') {
            return [];
        }

        if ($node->namespacedName === null) {
            return [];
        }

        $fullyQualifiedClassName = $node->namespacedName->toString();

        if (!$this->reflectionProvider->hasClass($fullyQualifiedClassName)) {
            return [];
        }

        $classReflection = $this->reflectionProvider->getClass($fullyQualifiedClassName);

        $errors = [];

        $isCommandHandler = $classReflection->implementsInterface(CommandHandler::class);
        $isQueryHandler = $classReflection->implementsInterface(QueryHandler::class);

        // Rule 1: Handler must implement QueryHandler or CommandHandler
        if (!$isCommandHandler && !$isQueryHandler) {
            $errors[] = RuleErrorBuilder::message(
                'Handler must implement QueryHandler or CommandHandler.',
            )->build();

            return $errors;
        }

        $namespace = $classReflection->getName();
        $handlerNamespace = $this->getNamespace($namespace);

        // Rule 2 & 3 & 4: Check __invoke method
        if ($classReflection->hasMethod('__invoke')) {
            $invokeMethod = $classReflection->getMethod('__invoke', $scope);
            $variants = $invokeMethod->getVariants();

            if (\count($variants) > 0) {
                $variant = $variants[0];
                $parameters = $variant->getParameters();

                // Rule 3: Must have exactly one parameter named "input"
                if (\count($parameters) !== 1) {
                    $errors[] = RuleErrorBuilder::message(
                        'Handler __invoke method must have exactly one parameter.',
                    )->build();
                } else {
                    $parameter = $parameters[0];
                    $parameterName = $parameter->getName();
                    $parameterType = $parameter->getType();

                    if ($parameterName !== 'input') {
                        $errors[] = RuleErrorBuilder::message(
                            \sprintf('Handler __invoke parameter must be named "input", got "%s".', $parameterName),
                        )->build();
                    }

                    // Check parameter type
                    $inputClassName = null;
                    foreach ($parameterType->getObjectClassNames() as $objectClassName) {
                        $inputClassName = $objectClassName;
                        break;
                    }

                    if ($inputClassName !== null) {
                        // Rule 2: Input must be in the same namespace
                        $inputNamespace = $this->getNamespace($inputClassName);
                        if ($inputNamespace !== $handlerNamespace) {
                            $errors[] = RuleErrorBuilder::message(
                                \sprintf(
                                    'Input class must be in the same namespace as Handler. Expected "%s", got "%s".',
                                    $handlerNamespace,
                                    $inputNamespace,
                                ),
                            )->build();
                        }

                        // Rule 4: Input must implement correct interface
                        if (class_exists($inputClassName) || interface_exists($inputClassName)) {
                            $inputReflection = new \ReflectionClass($inputClassName);

                            if ($isCommandHandler && !$inputReflection->implementsInterface(Command::class)) {
                                $errors[] = RuleErrorBuilder::message(
                                    'Input must implement Command interface for CommandHandler.',
                                )->build();
                            }

                            if ($isQueryHandler && !$inputReflection->implementsInterface(Query::class)) {
                                $errors[] = RuleErrorBuilder::message(
                                    'Input must implement Query interface for QueryHandler.',
                                )->build();
                            }
                        }
                    }
                }

                // Rule 5: QueryHandler must return an Output
                if ($isQueryHandler) {
                    $returnType = $variant->getReturnType();

                    if ($returnType->isVoid()->yes()) {
                        $errors[] = RuleErrorBuilder::message(
                            'QueryHandler must return an Output, not void.',
                        )->build();
                    } else {
                        $returnClassNames = $returnType->getObjectClassNames();
                        if (\count($returnClassNames) > 0) {
                            $outputClassName = $returnClassNames[0];
                            $outputNamespace = $this->getNamespace($outputClassName);

                            // Rule 2: Output must be in the same namespace
                            if ($outputNamespace !== $handlerNamespace) {
                                $errors[] = RuleErrorBuilder::message(
                                    \sprintf(
                                        'Output class must be in the same namespace as Handler. Expected "%s", got "%s".',
                                        $handlerNamespace,
                                        $outputNamespace,
                                    ),
                                )->build();
                            }
                        }
                    }
                }
            }
        } else {
            $errors[] = RuleErrorBuilder::message(
                'Handler must have an __invoke method.',
            )->build();
        }

        return $errors;
    }

    private function getNamespace(string $fqcn): string
    {
        $parts = explode('\\', $fqcn);
        array_pop($parts);

        return implode('\\', $parts);
    }
}
