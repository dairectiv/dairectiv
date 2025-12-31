<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveArchived;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class ArchiveRuleTest extends IntegrationTestCase
{
    public function testItShouldArchiveDraftRule(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->archiveRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [],
            'content'     => null,
            'state'       => 'archived',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchivePublishedRule(): void
    {
        $rule = self::draftRuleEntity();
        $rule->publish();
        $this->persistEntity($rule);

        $this->archiveRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [],
            'content'     => null,
            'state'       => 'archived',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchiveRuleWithContent(): void
    {
        $rule = self::draftRuleEntity();
        $rule->updateContent('Some rule content');
        $this->persistEntity($rule);

        $this->archiveRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [],
            'content'     => 'Some rule content',
            'state'       => 'archived',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldArchiveRuleWithExamples(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'good code', 'bad code', 'explanation');
        $this->persistEntity($rule);

        $this->archiveRule((string) $rule->id);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [
                [
                    'id'          => $example->id->toString(),
                    'good'        => 'good code',
                    'bad'         => 'bad code',
                    'explanation' => 'explanation',
                    'createdAt'   => Chronos::now()->toIso8601String(),
                    'updatedAt'   => Chronos::now()->toIso8601String(),
                ],
            ],
            'content'   => null,
            'state'     => 'archived',
            'updatedAt' => Chronos::now()->toIso8601String(),
            'createdAt' => Chronos::now()->toIso8601String(),
        ]);

        self::assertDomainEventHasBeenDispatched(DirectiveArchived::class);
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->archiveRule('non-existent-rule');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn404WhenWorkflowIdProvided(): void
    {
        $workflow = self::draftWorkflowEntity();
        $this->persistEntity($workflow);

        $this->archiveRule((string) $workflow->id);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn409WhenRuleAlreadyArchived(): void
    {
        $rule = self::draftRuleEntity();
        $rule->archive();
        $this->persistEntity($rule);

        $this->archiveRule((string) $rule->id);

        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }

    private function archiveRule(string $id): void
    {
        DomainEventQueue::reset();
        $this->postJson(\sprintf('/api/authoring/rules/%s/archive', $id));
    }
}
