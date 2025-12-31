<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class GetRuleTest extends IntegrationTestCase
{
    public function testItShouldGetRuleWithoutContentAndExamples(): void
    {
        $rule = self::draftRuleEntity();
        $this->persistEntity($rule);

        $this->getRule();

        self::assertResponseIsSuccessful();

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [],
            'content'     => null,
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetRuleWithContent(): void
    {
        $rule = self::draftRuleEntity();
        $rule->updateContent('Some rule content');
        $this->persistEntity($rule);

        $this->getRule();

        self::assertResponseIsSuccessful();

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [],
            'content'     => 'Some rule content',
            'state'       => 'draft',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetRuleWithExamples(): void
    {
        $rule = self::draftRuleEntity();
        $example1 = Example::create($rule, 'good1', 'bad1', 'explanation1');
        $example2 = Example::create($rule, 'good2', 'bad2', 'explanation2');
        $this->persistEntity($rule);

        $this->getRule();

        self::assertResponseIsSuccessful();

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [
                [
                    'id'          => $example1->id->toString(),
                    'good'        => 'good1',
                    'bad'         => 'bad1',
                    'explanation' => 'explanation1',
                    'createdAt'   => Chronos::now()->toIso8601String(),
                    'updatedAt'   => Chronos::now()->toIso8601String(),
                ],
                [
                    'id'          => $example2->id->toString(),
                    'good'        => 'good2',
                    'bad'         => 'bad2',
                    'explanation' => 'explanation2',
                    'createdAt'   => Chronos::now()->toIso8601String(),
                    'updatedAt'   => Chronos::now()->toIso8601String(),
                ],
            ],
            'content'   => null,
            'state'     => 'draft',
            'updatedAt' => Chronos::now()->toIso8601String(),
            'createdAt' => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetRuleWithContentAndExamples(): void
    {
        $rule = self::draftRuleEntity();
        $rule->updateContent('Complete rule content');
        $example = Example::create($rule, 'good', 'bad', 'explanation');
        $this->persistEntity($rule);

        $this->getRule();

        self::assertResponseIsSuccessful();

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [
                [
                    'id'          => $example->id->toString(),
                    'good'        => 'good',
                    'bad'         => 'bad',
                    'explanation' => 'explanation',
                    'createdAt'   => Chronos::now()->toIso8601String(),
                    'updatedAt'   => Chronos::now()->toIso8601String(),
                ],
            ],
            'content'   => 'Complete rule content',
            'state'     => 'draft',
            'updatedAt' => Chronos::now()->toIso8601String(),
            'createdAt' => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetPublishedRule(): void
    {
        $rule = self::draftRuleEntity();
        $rule->publish();
        $this->persistEntity($rule);

        $this->getRule();

        self::assertResponseIsSuccessful();

        self::assertResponseReturnsJson([
            'id'          => (string) $rule->id,
            'name'        => $rule->name,
            'description' => $rule->description,
            'examples'    => [],
            'content'     => null,
            'state'       => 'published',
            'updatedAt'   => Chronos::now()->toIso8601String(),
            'createdAt'   => Chronos::now()->toIso8601String(),
        ]);
    }

    public function testItShouldGetArchivedRule(): void
    {
        $rule = self::draftRuleEntity();
        $rule->archive();
        $this->persistEntity($rule);

        $this->getRule();

        self::assertResponseIsSuccessful();

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
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->getRule('non-existent-rule');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    private function getRule(string $id = 'rule-id'): void
    {
        $this->getJson(\sprintf('/api/authoring/rules/%s', $id));
    }
}
