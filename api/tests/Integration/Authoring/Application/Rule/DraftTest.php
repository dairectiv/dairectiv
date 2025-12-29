<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Dairectiv\Authoring\Application\Rule\Draft\Input;
use Dairectiv\Authoring\Application\Rule\Draft\Output;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Rule\RuleContent;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExamples;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class DraftTest extends IntegrationTestCase
{
    public function testItShouldDraftRuleWithAllExampleTypes(): void
    {
        $output = $this->execute(
            new Input(
                'rule-all-examples',
                'Rule Name',
                'Rule Description',
                'Rule Content',
                [
                    [
                        'good'        => 'This is a good example.',
                        'bad'         => 'This is a bad example.',
                        'explanation' => 'This explains the difference.',
                    ],
                    [
                        'good'        => 'Another good example.',
                        'explanation' => 'This explains the difference.',
                    ],
                    [
                        'bad' => 'Another bad example.',
                    ],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-all-examples')], true);

        self::assertEquals($output->rule, $rule);
        $this->assertRule(
            $rule,
            'Rule Name',
            'Rule Description',
            'Rule Content',
            [
                [
                    'good'        => 'This is a good example.',
                    'bad'         => 'This is a bad example.',
                    'explanation' => 'This explains the difference.',
                ],
                [
                    'good'        => 'Another good example.',
                    'explanation' => 'This explains the difference.',
                ],
                [
                    'bad' => 'Another bad example.',
                ],
            ],
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithoutExamples(): void
    {
        $output = $this->execute(
            new Input(
                'rule-no-examples',
                'Minimal Rule',
                'Minimal Description',
                'Minimal Content',
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-no-examples')], true);

        self::assertEquals($output->rule, $rule);
        $this->assertRule(
            $rule,
            'Minimal Rule',
            'Minimal Description',
            'Minimal Content',
            [],
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithEmptyExamplesArray(): void
    {
        $output = $this->execute(
            new Input(
                'rule-empty-examples',
                'Rule With Empty Examples',
                'Description',
                'Content',
                [],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-empty-examples')], true);

        self::assertEquals($output->rule, $rule);
        $this->assertRule(
            $rule,
            'Rule With Empty Examples',
            'Description',
            'Content',
            [],
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithOnlyGoodExamples(): void
    {
        $output = $this->execute(
            new Input(
                'rule-good-only',
                'Good Examples Rule',
                'Description',
                'Content',
                [
                    ['good' => 'First good example.'],
                    ['good' => 'Second good example.', 'explanation' => 'Why this is good.'],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-good-only')], true);

        self::assertEquals($output->rule, $rule);
        $this->assertRule(
            $rule,
            'Good Examples Rule',
            'Description',
            'Content',
            [
                ['good' => 'First good example.'],
                ['good' => 'Second good example.', 'explanation' => 'Why this is good.'],
            ],
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithOnlyBadExamples(): void
    {
        $output = $this->execute(
            new Input(
                'rule-bad-only',
                'Bad Examples Rule',
                'Description',
                'Content',
                [
                    ['bad' => 'First bad example.'],
                    ['bad' => 'Second bad example.', 'explanation' => 'Why this is bad.'],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-bad-only')], true);

        self::assertEquals($output->rule, $rule);
        $this->assertRule(
            $rule,
            'Bad Examples Rule',
            'Description',
            'Content',
            [
                ['bad' => 'First bad example.'],
                ['bad' => 'Second bad example.', 'explanation' => 'Why this is bad.'],
            ],
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithTransformationExamples(): void
    {
        $output = $this->execute(
            new Input(
                'rule-transformations',
                'Transformation Rule',
                'Description',
                'Content',
                [
                    [
                        'good'        => 'Correct implementation.',
                        'bad'         => 'Incorrect implementation.',
                        'explanation' => 'Explains the transformation.',
                    ],
                    [
                        'good' => 'Better approach.',
                        'bad'  => 'Worse approach.',
                    ],
                ],
            ),
        );

        self::assertInstanceOf(Output::class, $output);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-transformations')], true);

        self::assertEquals($output->rule, $rule);
        $this->assertRule(
            $rule,
            'Transformation Rule',
            'Description',
            'Content',
            [
                [
                    'good'        => 'Correct implementation.',
                    'bad'         => 'Incorrect implementation.',
                    'explanation' => 'Explains the transformation.',
                ],
                [
                    'good' => 'Better approach.',
                    'bad'  => 'Worse approach.',
                ],
            ],
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    /**
     * @param list<array{good?: ?string, bad?: ?string, explanation?: ?string}> $expectedExamples
     */
    private function assertRule(
        Rule $rule,
        string $expectedName,
        string $expectedDescription,
        string $expectedContent,
        array $expectedExamples,
    ): void {
        self::assertEquals(
            DirectiveMetadata::create(
                DirectiveName::fromString($expectedName),
                DirectiveDescription::fromString($expectedDescription),
            ),
            $rule->metadata,
        );

        self::assertEquals(
            RuleContent::fromString($expectedContent),
            $rule->content,
        );

        if ([] === $expectedExamples) {
            self::assertEquals(RuleExamples::empty(), $rule->examples);
        } else {
            self::assertEquals(
                RuleExamples::fromArray(['examples' => $expectedExamples]),
                $rule->examples,
            );
        }
    }
}
