<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectivePublished;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class PublishRuleTest extends IntegrationTestCase
{
    public function testItShouldPublishDraftRule(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->publishRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
    }

    public function testItShouldPublishRuleWithContent(): void
    {
        $rule = self::draftRuleEntity();
        $rule->updateContent('Some rule content');
        $this->persistEntity($rule);

        $this->publishRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
    }

    public function testItShouldPublishRuleWithExamples(): void
    {
        $rule = self::draftRuleEntity();
        Example::create($rule, 'good code', 'bad code', 'explanation');
        $this->persistEntity($rule);

        $this->publishRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->publishRule('non-existent-rule');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenWorkflowIdProvided(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->publishRule((string) $workflow->id);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn409WhenRuleAlreadyPublished(): void
    {
        $rule = self::draftRuleEntity();
        $rule->publish();
        $this->persistEntity($rule);

        $this->publishRule((string) $rule->id);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    public function testItShouldReturn409WhenRuleIsArchived(): void
    {
        $rule = self::draftRuleEntity();
        $rule->archive();
        $this->persistEntity($rule);

        $this->publishRule((string) $rule->id);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    private function publishRule(string $id): void
    {
        DomainEventQueue::reset();
        $this->putJson(\sprintf('/api/authoring/rules/%s/publish', $id));
    }
}
