<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Dairectiv\Authoring\Application\Rule\GetRule\Input;
use Dairectiv\Authoring\Application\Rule\GetRule\Output;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class GetRuleTest extends IntegrationTestCase
{
    public function testItShouldGetRule(): void
    {
        $rule = self::draftRuleEntity(id: 'my-rule', name: 'My Rule', description: 'A description');
        $rule->updateContent('Some content');
        $this->persistEntity($rule);

        $output = $this->executeGetRule('my-rule');

        self::assertSame('my-rule', (string) $output->rule->id);
        self::assertSame('My Rule', $output->rule->name);
        self::assertSame('A description', $output->rule->description);
        self::assertSame('Some content', $output->rule->content);
        self::assertSame(DirectiveState::Draft, $output->rule->state);
    }

    public function testItShouldGetRuleWithExamples(): void
    {
        $rule = self::draftRuleEntity(id: 'rule-with-examples');
        Example::create($rule, 'good1', 'bad1', 'explanation1');
        Example::create($rule, 'good2', 'bad2', 'explanation2');
        $this->persistEntity($rule);

        $output = $this->executeGetRule('rule-with-examples');

        self::assertCount(2, $output->rule->examples);
    }

    public function testItShouldThrowExceptionWhenRuleNotFound(): void
    {
        $this->expectException(RuleNotFoundException::class);
        $this->expectExceptionMessage('Rule with ID non-existent-rule not found.');

        $this->executeGetRule('non-existent-rule');
    }

    public function testItShouldThrowExceptionWhenRuleIsDeleted(): void
    {
        $rule = self::draftRuleEntity(id: 'deleted-rule');
        $rule->delete();
        $this->persistEntity($rule);

        $this->expectException(RuleNotFoundException::class);

        $this->executeGetRule('deleted-rule');
    }

    private function executeGetRule(string $id): Output
    {
        $output = $this->fetch(new Input($id));

        self::assertInstanceOf(Output::class, $output);

        return $output;
    }
}
