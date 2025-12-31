<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDeleted;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class DeleteRuleTest extends IntegrationTestCase
{
    public function testItShouldDeleteDraftRule(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->deleteRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldDeletePublishedRule(): void
    {
        $rule = self::draftRuleEntity();
        $rule->publish();
        $this->persistEntity($rule);

        $this->deleteRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldDeleteArchivedRule(): void
    {
        $rule = self::draftRuleEntity();
        $rule->archive();
        $this->persistEntity($rule);

        $this->deleteRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldDeleteRuleWithContent(): void
    {
        $rule = self::draftRuleEntity();
        $rule->updateContent('Some rule content');
        $this->persistEntity($rule);

        $this->deleteRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldDeleteRuleWithExamples(): void
    {
        $rule = self::draftRuleEntity();
        Example::create($rule, 'good code', 'bad code', 'explanation');
        $this->persistEntity($rule);

        $this->deleteRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveDeleted::class);
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->deleteRule('non-existent-rule');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenWorkflowIdProvided(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->deleteRule((string) $workflow->id);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function deleteRule(string $id): void
    {
        DomainEventQueue::reset();
        $this->deleteJson(\sprintf('/api/authoring/rules/%s', $id));
    }
}
