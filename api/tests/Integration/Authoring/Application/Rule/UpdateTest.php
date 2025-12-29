<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Dairectiv\Authoring\Application\Rule\Update\Input;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Rule\RuleFactory;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Zenstruck\Foundry\Test\Factories;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class UpdateTest extends IntegrationTestCase
{
    use Factories;

    public function testItShouldUpdateMetadataOnly(): void
    {
        $rule = RuleFactory::new()->withId('rule-to-update')->create();

        $this->execute(new Input(
            id: $rule->id,
            name: 'Updated Name',
            description: 'Updated Description',
        ));

        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString($rule->id)], true);

        self::assertSame('Updated Name', (string) $rule->metadata->name);
        self::assertSame('Updated Description', (string) $rule->metadata->description);
    }

    public function testItShouldUpdateContentOnly(): void
    {
        $rule = RuleFactory::new()->withId('rule-to-update')->create();

        $this->execute(new Input(
            id: $rule->id,
            content: 'Updated Content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString($rule->id)], true);

        self::assertSame('Updated Content', (string) $rule->content);
    }

    public function testItShouldUpdateExamplesOnly(): void
    {
        $rule = RuleFactory::new()->withId('rule-to-update')->create();

        $this->execute(new Input(
            id: $rule->id,
            examples: [
                ['good' => 'Good example', 'explanation' => 'This is good'],
                ['bad' => 'Bad example', 'explanation' => 'This is bad'],
            ],
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString($rule->id)], true);

        self::assertCount(2, $rule->examples);
    }

    public function testItShouldClearExamples(): void
    {
        $rule = RuleFactory::new()->withId('rule-to-update')->create();

        $this->execute(new Input(
            id: $rule->id,
            examples: [],
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString($rule->id)], true);

        self::assertCount(0, $rule->examples);
    }

    public function testItShouldUpdateBothMetadataAndContent(): void
    {
        $rule = RuleFactory::new()->withId('rule-to-update')->create();

        $this->execute(new Input(
            id: $rule->id,
            name: 'Updated Name',
            content: 'Updated Content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        $rule = $this->findEntity(Rule::class, ['id' => DirectiveId::fromString($rule->id)], true);

        self::assertSame('Updated Name', (string) $rule->metadata->name);
        self::assertSame('Updated Content', (string) $rule->content);
    }

    public function testItShouldThrowExceptionWhenRuleNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input(id: 'non-existent-rule'));
    }
}
