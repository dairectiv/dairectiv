<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Tests;

use Dairectiv\PHPStan\Rules\UseCaseRule;
use PHPStan\Rules\Rule as TRule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<UseCaseRule>
 */
#[Group('tools')]
#[Group('phpstan')]
final class UseCaseRuleTest extends RuleTestCase
{
    protected function getRule(): TRule
    {
        return new UseCaseRule(
            $this->createReflectionProvider(),
        );
    }

    public function testItShouldPassForValidCommandHandler(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/UseCaseRule/ValidCommandHandler/Handler.php'],
            [],
        );
    }

    public function testItShouldPassForValidQueryHandler(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/UseCaseRule/ValidQueryHandler/Handler.php'],
            [],
        );
    }

    public function testItShouldReportErrorWhenHandlerDoesNotImplementInterface(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/UseCaseRule/InvalidNoInterface/Handler.php'],
            [
                ['Handler must implement QueryHandler or CommandHandler.', 7],
            ],
        );
    }

    public function testItShouldReportErrorWhenInputHasWrongInterface(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/UseCaseRule/InvalidWrongInputInterface/Handler.php'],
            [
                ['Input must implement Command interface for CommandHandler.', 9],
            ],
        );
    }

    public function testItShouldReportErrorWhenQueryHandlerReturnsVoid(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/UseCaseRule/InvalidQueryHandlerVoid/Handler.php'],
            [
                ['QueryHandler must return an Output, not void.', 9],
            ],
        );
    }

    public function testItShouldReportErrorWhenParameterNameIsWrong(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/UseCaseRule/InvalidWrongParameterName/Handler.php'],
            [
                ['Handler __invoke parameter must be named "input", got "command".', 9],
            ],
        );
    }
}
