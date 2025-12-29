<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Rule\RuleContent;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExamples;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class DraftRuleTest extends IntegrationTestCase
{
    /**
     * @return iterable<string, array{payload: array<string, mixed>, expectedViolations: array<array{propertyPath: string, title: string}>}>
     */
    public static function provideInvalidPayloads(): iterable
    {
        yield 'id not int kebab case' => [
            'payload'            => ['id' => 'Non Kebab Case'],
            'expectedViolations' => [
                [
                    'propertyPath' => 'id',
                    'title'        => 'This value is not valid.',
                ],
            ],
        ];
        yield 'blank id' => [
            'payload'            => ['id' => ''],
            'expectedViolations' => [
                [
                    'propertyPath' => 'id',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'blank name' => [
            'payload'            => ['name' => ''],
            'expectedViolations' => [
                [
                    'propertyPath' => 'name',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'too long name' => [
            'payload'            => ['name' => self::faker()->realTextBetween(256, 500)],
            'expectedViolations' => [
                [
                    'propertyPath' => 'name',
                    'title'        => 'This value is too long. It should have 255 characters or less.',
                ],
            ],
        ];
        yield 'blank description' => [
            'payload'            => ['description' => ''],
            'expectedViolations' => [
                [
                    'propertyPath' => 'description',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'too long description' => [
            'payload'            => ['description' => self::faker()->realTextBetween(501, 1000)],
            'expectedViolations' => [
                [
                    'propertyPath' => 'description',
                    'title'        => 'This value is too long. It should have 500 characters or less.',
                ],
            ],
        ];
        yield 'blank content' => [
            'payload'            => ['content' => ''],
            'expectedViolations' => [
                [
                    'propertyPath' => 'content',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'blank example good' => [
            'payload'            => ['examples' => [['good' => '']]],
            'expectedViolations' => [
                [
                    'propertyPath' => 'examples[0].good',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'blank example bad' => [
            'payload'            => ['examples' => [['bad' => '']]],
            'expectedViolations' => [
                [
                    'propertyPath' => 'examples[0].bad',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'missing good and bad' => [
            'payload'            => ['examples' => [['bad' => null, 'good' => null]]],
            'expectedViolations' => [
                [
                    'propertyPath' => 'examples[0].good',
                    'title'        => 'This value should not be blank.',
                ],
                [
                    'propertyPath' => 'examples[0].bad',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
        yield 'blank example explanation' => [
            'payload'            => ['examples' => [['explanation' => '']]],
            'expectedViolations' => [
                [
                    'propertyPath' => 'examples[0].explanation',
                    'title'        => 'This value should not be blank.',
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<array{propertyPath: string, title: string}> $expectedViolations
     */
    #[DataProvider('provideInvalidPayloads')]
    public function testItShouldNotDraftRuleDueToUnprocessableEntity(array $payload, array $expectedViolations): void
    {
        $basePayload = [
            'id'          => 'rule-all-examples',
            'name'        => 'Rule Name',
            'description' => 'Rule Description',
            'content'     => 'Rule Content',
            'examples'    => [
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
        ];

        $this->postJson('/api/authoring/rules', self::override($basePayload, $payload));

        self::assertResponseIsUnprocessable();
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsUnprocessableEntity($expectedViolations);
    }

    public function testItShouldDraftRuleWithAllExampleTypes(): void
    {
        $this->postJson('/api/authoring/rules', [
            'id'          => 'rule-all-examples',
            'name'        => 'Rule Name',
            'description' => 'Rule Description',
            'content'     => 'Rule Content',
            'examples'    => [
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
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'rule-all-examples',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Rule Name',
            'description' => 'Rule Description',
            'content'     => 'Rule Content',
            'examples'    => [
                [
                    'good'        => 'This is a good example.',
                    'bad'         => 'This is a bad example.',
                    'explanation' => 'This explains the difference.',
                ],
                [
                    'good'        => 'Another good example.',
                    'bad'         => null,
                    'explanation' => 'This explains the difference.',
                ],
                [
                    'good'        => null,
                    'bad'         => 'Another bad example.',
                    'explanation' => null,
                ],
            ],
        ]);

        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-all-examples')], true);

        $this->assertRule(
            $rule,
            'Rule Name',
            'Rule Description',
            'Rule Content',
            3,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithoutExamples(): void
    {
        $this->postJson('/api/authoring/rules', [
            'id'          => 'rule-no-examples',
            'name'        => 'Minimal Rule',
            'description' => 'Minimal Description',
            'content'     => 'Minimal Content',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'rule-no-examples',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Minimal Rule',
            'description' => 'Minimal Description',
            'content'     => 'Minimal Content',
            'examples'    => [],
        ]);

        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-no-examples')], true);

        $this->assertRule(
            $rule,
            'Minimal Rule',
            'Minimal Description',
            'Minimal Content',
            0,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithEmptyExamplesArray(): void
    {
        $this->postJson('/api/authoring/rules', [
            'id'          => 'rule-empty-examples',
            'name'        => 'Rule With Empty Examples',
            'description' => 'Description',
            'content'     => 'Content',
            'examples'    => [],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'rule-empty-examples',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Rule With Empty Examples',
            'description' => 'Description',
            'content'     => 'Content',
            'examples'    => [],
        ]);

        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-empty-examples')], true);

        $this->assertRule(
            $rule,
            'Rule With Empty Examples',
            'Description',
            'Content',
            0,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithOnlyGoodExamples(): void
    {
        $this->postJson('/api/authoring/rules', [
            'id'          => 'rule-good-only',
            'name'        => 'Good Examples Rule',
            'description' => 'Description',
            'content'     => 'Content',
            'examples'    => [
                ['good' => 'First good example.'],
                ['good' => 'Second good example.', 'explanation' => 'Why this is good.'],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'rule-good-only',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Good Examples Rule',
            'description' => 'Description',
            'content'     => 'Content',
            'examples'    => [
                ['good' => 'First good example.', 'bad' => null, 'explanation' => null],
                ['good' => 'Second good example.', 'bad' => null, 'explanation' => 'Why this is good.'],
            ],
        ]);

        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-good-only')], true);

        $this->assertRule(
            $rule,
            'Good Examples Rule',
            'Description',
            'Content',
            2,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithOnlyBadExamples(): void
    {
        $this->postJson('/api/authoring/rules', [
            'id'          => 'rule-bad-only',
            'name'        => 'Bad Examples Rule',
            'description' => 'Description',
            'content'     => 'Content',
            'examples'    => [
                ['bad' => 'First bad example.'],
                ['bad' => 'Second bad example.', 'explanation' => 'Why this is bad.'],
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'rule-bad-only',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Bad Examples Rule',
            'description' => 'Description',
            'content'     => 'Content',
            'examples'    => [
                ['good' => null, 'bad' => 'First bad example.', 'explanation' => null],
                ['good' => null, 'bad' => 'Second bad example.', 'explanation' => 'Why this is bad.'],
            ],
        ]);

        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-bad-only')], true);

        $this->assertRule(
            $rule,
            'Bad Examples Rule',
            'Description',
            'Content',
            2,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftRuleWithTransformationExamples(): void
    {
        $this->postJson('/api/authoring/rules', [
            'id'          => 'rule-transformations',
            'name'        => 'Transformation Rule',
            'description' => 'Description',
            'content'     => 'Content',
            'examples'    => [
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
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(201);
        self::assertResponseHeaderSame('content-type', 'application/json');
        self::assertResponseReturnsJson([
            'id'          => 'rule-transformations',
            'state'       => 'draft',
            'createdAt'   => Chronos::now()->toIso8601String(),
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'name'        => 'Transformation Rule',
            'description' => 'Description',
            'content'     => 'Content',
            'examples'    => [
                [
                    'good'        => 'Correct implementation.',
                    'bad'         => 'Incorrect implementation.',
                    'explanation' => 'Explains the transformation.',
                ],
                [
                    'good'        => 'Better approach.',
                    'bad'         => 'Worse approach.',
                    'explanation' => null,
                ],
            ],
        ]);

        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString('rule-transformations')], true);

        $this->assertRule(
            $rule,
            'Transformation Rule',
            'Description',
            'Content',
            2,
        );

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    private function assertRule(
        Rule $rule,
        string $expectedName,
        string $expectedDescription,
        string $expectedContent,
        int $expectedExamplesCount,
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

        if (0 === $expectedExamplesCount) {
            self::assertEquals(RuleExamples::empty(), $rule->examples);
        } else {
            self::assertCount($expectedExamplesCount, $rule->examples);
        }
    }
}
