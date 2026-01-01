<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Rule;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class ListRulesTest extends IntegrationTestCase
{
    public function testItShouldReturnEmptyListWhenNoRules(): void
    {
        $this->listRules();

        self::assertResponseIsSuccessful();
        self::assertResponseReturnsJson([
            'items'      => [],
            'pagination' => [
                'page'            => 1,
                'limit'           => 20,
                'total'           => 0,
                'totalPages'      => 0,
                'hasNextPage'     => false,
                'hasPreviousPage' => false,
            ],
        ]);
    }

    public function testItShouldReturnPaginatedList(): void
    {
        $this->createRules(25);

        $this->listRules('?page=1&limit=10');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(10, $response['items']);
        self::assertSame(25, $response['pagination']['total']);
        self::assertSame(3, $response['pagination']['totalPages']);
        self::assertTrue($response['pagination']['hasNextPage']);
        self::assertFalse($response['pagination']['hasPreviousPage']);
    }

    public function testItShouldReturnSecondPage(): void
    {
        $this->createRules(25);

        $this->listRules('?page=2&limit=10');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(10, $response['items']);
        self::assertSame(2, $response['pagination']['page']);
        self::assertTrue($response['pagination']['hasNextPage']);
        self::assertTrue($response['pagination']['hasPreviousPage']);
    }

    public function testItShouldReturnLastPage(): void
    {
        $this->createRules(25);

        $this->listRules('?page=3&limit=10');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(5, $response['items']);
        self::assertFalse($response['pagination']['hasNextPage']);
        self::assertTrue($response['pagination']['hasPreviousPage']);
    }

    public function testItShouldFilterBySearchInName(): void
    {
        $rule1 = Rule::draft(
            DirectiveId::fromString('rule-authentication'),
            'Authentication rules',
            'Rules for authentication',
        );
        $rule2 = Rule::draft(
            DirectiveId::fromString('rule-authorization'),
            'Authorization rules',
            'Rules for authorization',
        );
        $rule3 = Rule::draft(
            DirectiveId::fromString('rule-validation'),
            'Validation rules',
            'Rules for validation',
        );
        $this->persistEntity($rule1);
        $this->persistEntity($rule2);
        $this->persistEntity($rule3);

        $this->listRules('?search=Auth');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(2, $response['items']);
        self::assertSame(2, $response['pagination']['total']);
    }

    public function testItShouldFilterBySearchInDescription(): void
    {
        $rule1 = Rule::draft(
            DirectiveId::fromString('rule-special'),
            'Rule special',
            'Contains special keyword',
        );
        $rule2 = Rule::draft(
            DirectiveId::fromString('rule-normal'),
            'Rule normal',
            'Normal description',
        );
        $this->persistEntity($rule1);
        $this->persistEntity($rule2);

        $this->listRules('?search=special');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(1, $response['items']);
        self::assertSame('rule-special', $response['items'][0]['id']);
    }

    public function testItShouldFilterByState(): void
    {
        $rule1 = self::draftRuleEntity('rule-draft');
        $rule2 = self::draftRuleEntity('rule-published');
        $rule2->publish();
        $rule3 = self::draftRuleEntity('rule-archived');
        $rule3->archive();
        $this->persistEntity($rule1);
        $this->persistEntity($rule2);
        $this->persistEntity($rule3);

        $this->listRules('?state=draft');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(1, $response['items']);
        self::assertSame('rule-draft', $response['items'][0]['id']);
    }

    public function testItShouldReturnPublishedRules(): void
    {
        $rule1 = self::draftRuleEntity('rule-draft-one');
        $rule2 = self::draftRuleEntity('rule-published-one');
        $rule2->publish();
        $this->persistEntity($rule1);
        $this->persistEntity($rule2);

        $this->listRules('?state=published');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(1, $response['items']);
        self::assertSame('rule-published-one', $response['items'][0]['id']);
    }

    public function testItShouldSortByNameAscending(): void
    {
        $rule1 = Rule::draft(
            DirectiveId::fromString('rule-zebra'),
            'Zebra rules',
            'Description',
        );
        $rule2 = Rule::draft(
            DirectiveId::fromString('rule-alpha'),
            'Alpha rules',
            'Description',
        );
        $this->persistEntity($rule1);
        $this->persistEntity($rule2);

        $this->listRules('?sortBy=name&sortOrder=asc');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertSame('rule-alpha', $response['items'][0]['id']);
        self::assertSame('rule-zebra', $response['items'][1]['id']);
    }

    public function testItShouldSortByNameDescending(): void
    {
        $rule1 = Rule::draft(
            DirectiveId::fromString('rule-alpha-desc'),
            'Alpha rules',
            'Description',
        );
        $rule2 = Rule::draft(
            DirectiveId::fromString('rule-zebra-desc'),
            'Zebra rules',
            'Description',
        );
        $this->persistEntity($rule1);
        $this->persistEntity($rule2);

        $this->listRules('?sortBy=name&sortOrder=desc');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertSame('rule-zebra-desc', $response['items'][0]['id']);
        self::assertSame('rule-alpha-desc', $response['items'][1]['id']);
    }

    public function testItShouldCombineFilterAndSort(): void
    {
        $rule1 = Rule::draft(
            DirectiveId::fromString('rule-zebra-auth'),
            'Zebra authentication',
            'Description',
        );
        $rule2 = Rule::draft(
            DirectiveId::fromString('rule-alpha-auth'),
            'Alpha authentication',
            'Description',
        );
        $rule3 = Rule::draft(
            DirectiveId::fromString('rule-validation-only'),
            'Validation rules',
            'Description',
        );
        $this->persistEntity($rule1);
        $this->persistEntity($rule2);
        $this->persistEntity($rule3);

        $this->listRules('?search=authentication&sortBy=name&sortOrder=asc');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(2, $response['items']);
        self::assertSame('rule-alpha-auth', $response['items'][0]['id']);
        self::assertSame('rule-zebra-auth', $response['items'][1]['id']);
    }

    public function testItShouldExcludeSoftDeletedRules(): void
    {
        $rule1 = self::draftRuleEntity('rule-active');
        $rule2 = self::draftRuleEntity('rule-deleted');
        $rule2->delete();
        $this->persistEntity($rule1);
        $this->persistEntity($rule2);

        $this->listRules();

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(1, $response['items']);
        self::assertSame('rule-active', $response['items'][0]['id']);
    }

    public function testItShouldEnforceLimitMax(): void
    {
        $this->createRules(5);

        $this->listRules('?limit=200');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertSame(100, $response['pagination']['limit']);
    }

    public function testItShouldEnforcePageMin(): void
    {
        $this->createRules(5);

        $this->listRules('?page=0');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertSame(1, $response['pagination']['page']);
    }

    private function listRules(string $query = ''): void
    {
        $this->getJson(\sprintf('/api/authoring/rules%s', $query));
    }

    private function createRules(int $count): void
    {
        $names = ['alpha', 'bravo', 'charlie', 'delta', 'echo', 'foxtrot', 'golf', 'hotel', 'india', 'juliet',
            'kilo', 'lima', 'mike', 'november', 'oscar', 'papa', 'quebec', 'romeo', 'sierra', 'tango',
            'uniform', 'victor', 'whiskey', 'xray', 'yankee', 'zulu'];

        for ($i = 0; $i < $count; ++$i) {
            $baseName = $names[$i % \count($names)];
            $suffix = (int) floor($i / \count($names));
            $id = $suffix > 0 ? \sprintf('rule-%s-%s', $baseName, $this->numberToWord($suffix)) : \sprintf('rule-%s', $baseName);

            $rule = Rule::draft(
                DirectiveId::fromString($id),
                \sprintf('Rule %s', $baseName),
                \sprintf('Description for rule %s', $baseName),
            );
            $this->persistEntity($rule);
        }
    }

    private function numberToWord(int $number): string
    {
        $words = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten'];

        return $words[($number - 1) % \count($words)] ?? 'extra';
    }

    /**
     * @return array{items: list<array{id: string, name: string, description: string, content: string|null, state: string, createdAt: string, updatedAt: string}>, pagination: array{page: int, limit: int, total: int, totalPages: int, hasNextPage: bool, hasPreviousPage: bool}}
     */
    private function getJsonResponse(): array
    {
        $response = self::getClient()?->getResponse();
        self::assertInstanceOf(Response::class, $response);

        $content = $response->getContent();
        self::assertIsString($content);

        /** @var array{items: list<array{id: string, name: string, description: string, content: string|null, state: string, createdAt: string, updatedAt: string}>, pagination: array{page: int, limit: int, total: int, totalPages: int, hasNextPage: bool, hasPreviousPage: bool}} */
        return \Safe\json_decode($content, true);
    }
}
