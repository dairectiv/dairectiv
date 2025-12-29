<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Tests;

use Dairectiv\PHPStan\Rules\TestNameRule;
use PHPStan\Rules\Rule as TRule;
use PHPStan\Testing\RuleTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @extends RuleTestCase<TestNameRule>
 */
#[Group('tools')]
#[Group('phpstan')]
final class TestNameRuleTest extends RuleTestCase
{
    protected function getRule(): TRule
    {
        return new TestNameRule();
    }

    public function testItShouldPassForValidTestNames(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/TestNameRule/ValidTestNames.php'],
            [],
        );
    }

    public function testItShouldReportErrorsForInvalidTestNames(): void
    {
        $this->analyse(
            [__DIR__ . '/../fixtures/TestNameRule/InvalidTestNames.php'],
            [
                [
                    'Test method "testSomething" must start with "testItShould".',
                    11,
                ],
                [
                    'Test method "testDoSomething" must start with "testItShould".',
                    15,
                ],
                [
                    'Test method "testHandleEdgeCase" must start with "testItShould".',
                    19,
                ],
            ],
        );
    }
}
