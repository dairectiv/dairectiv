<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\SharedKernel\Domain\Object\Event\DomainEventQueue;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class UpdateRuleExampleTest extends IntegrationTestCase
{
    public function testItShouldUpdateAllExampleFields(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), [
            'good'        => 'Updated good',
            'bad'         => 'Updated bad',
            'explanation' => 'Updated explanation',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldClearGoodField(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), [
            'good'        => null,
            'bad'         => 'Updated bad',
            'explanation' => 'Updated explanation',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldClearAllOptionalFields(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Original good', 'Original bad', 'Original explanation');
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), [
            'good'        => null,
            'bad'         => null,
            'explanation' => null,
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->updateExample('non-existent-rule', '00000000-0000-0000-0000-000000000000', [
            'good'        => 'Updated',
            'bad'         => null,
            'explanation' => null,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn400WhenExampleNotFound(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, '00000000-0000-0000-0000-000000000000', [
            'good'        => 'Updated',
            'bad'         => null,
            'explanation' => null,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testItShouldReturn400WhenRuleIsArchived(): void
    {
        $rule = self::draftRuleEntity();
        $example = Example::create($rule, 'Good', 'Bad');
        $rule->archive();
        $this->persistEntity($rule);

        $this->updateExample((string) $rule->id, $example->id->toString(), [
            'good'        => 'Updated',
            'bad'         => null,
            'explanation' => null,
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function updateExample(string $ruleId, string $exampleId, array $payload): void
    {
        DomainEventQueue::reset();
        $this->putJson(\sprintf('/api/authoring/rules/%s/examples/%s', $ruleId, $exampleId), $payload);
    }
}
