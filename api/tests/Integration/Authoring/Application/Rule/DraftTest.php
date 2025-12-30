<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Dairectiv\Authoring\Application\Rule\Draft\Input;
use Dairectiv\Authoring\Application\Rule\Draft\Output;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class DraftTest extends IntegrationTestCase
{
    public function testItShouldDraftRule(): void
    {
        $output = $this->executeDraftRule();

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);

        $rule = $output->rule;

        self::assertSame('my-rule', (string) $rule->id);
        self::assertSame('My Rule', $rule->name);
        self::assertSame('A description of my rule', $rule->description);
        self::assertSame(DirectiveState::Draft, $rule->state);
        self::assertTrue($rule->examples->isEmpty());
        self::assertNull($rule->content);

        $persistedRule = $this->findEntity(Rule::class, ['id' => $rule->id], true);

        self::assertSame('my-rule', (string) $persistedRule->id);
        self::assertSame('My Rule', $persistedRule->name);
        self::assertSame('A description of my rule', $persistedRule->description);
        self::assertSame(DirectiveState::Draft, $persistedRule->state);
        self::assertTrue($persistedRule->examples->isEmpty());
        self::assertNull($persistedRule->content);
    }

    public function testItShouldThrowExceptionWhenRuleAlreadyExists(): void
    {
        $rule = self::draftRule(id: 'my-rule');
        $this->persistEntity($rule);

        $this->expectException(DirectiveAlreadyExistsException::class);

        $this->executeDraftRule();
    }

    /**
     * @return iterable<string, array{name: string, expectedId: string}>
     */
    public static function provideNameToIdConversions(): iterable
    {
        yield 'spaces' => ['name' => 'My Awesome Rule', 'expectedId' => 'my-awesome-rule'];
        yield 'uppercase' => ['name' => 'UPPERCASE RULE', 'expectedId' => 'uppercaserule'];
        yield 'mixed case' => ['name' => 'MixedCase Rule Name', 'expectedId' => 'mixed-case-rule-name'];
        yield 'already kebab' => ['name' => 'already-kebab-case', 'expectedId' => 'already-kebab-case'];
        yield 'single word' => ['name' => 'Single', 'expectedId' => 'single'];
        yield 'camelCase' => ['name' => 'camelCaseRule', 'expectedId' => 'camel-case-rule'];
        yield 'PascalCase' => ['name' => 'PascalCaseRule', 'expectedId' => 'pascal-case-rule'];
        yield 'with numbers' => ['name' => 'Rule Version 2', 'expectedId' => 'rule-version2'];
    }

    #[DataProvider('provideNameToIdConversions')]
    public function testItShouldGenerateKebabCaseIdFromName(string $name, string $expectedId): void
    {
        $output = $this->executeDraftRule($name);

        self::assertSame($expectedId, (string) $output->rule->id);
        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldDraftMultipleDistinctRules(): void
    {
        $output1 = $this->executeDraftRule('First Rule', 'First description');
        $output2 = $this->executeDraftRule('Second Rule', 'Second description');
        $output3 = $this->executeDraftRule('Third Rule', 'Third description');

        self::assertSame('first-rule', (string) $output1->rule->id);
        self::assertSame('second-rule', (string) $output2->rule->id);
        self::assertSame('third-rule', (string) $output3->rule->id);

        // Verify all are persisted
        $persistedRule1 = $this->findEntity(Rule::class, ['id' => $output1->rule->id], true);
        $persistedRule2 = $this->findEntity(Rule::class, ['id' => $output2->rule->id], true);
        $persistedRule3 = $this->findEntity(Rule::class, ['id' => $output3->rule->id], true);

        self::assertSame('First Rule', $persistedRule1->name);
        self::assertSame('Second Rule', $persistedRule2->name);
        self::assertSame('Third Rule', $persistedRule3->name);

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class, 3);
    }

    public function testItShouldPreserveOriginalNameAndDescription(): void
    {
        $output = $this->executeDraftRule('  Rule With  Extra   Spaces  ', 'Description with trailing space ');

        // ID should be kebab-cased from trimmed name
        self::assertSame('rule-with-extra-spaces', (string) $output->rule->id);

        // But original name and description should be preserved as provided
        self::assertSame('  Rule With  Extra   Spaces  ', $output->rule->name);
        self::assertSame('Description with trailing space ', $output->rule->description);

        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    private function executeDraftRule(
        string $name = 'My Rule',
        string $description = 'A description of my rule'
    ): Output {
        $output = $this->execute(new Input($name, $description));

        self::assertInstanceOf(Output::class, $output);

        return $output;
    }
}
