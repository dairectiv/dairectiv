<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\Workflow;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class ListWorkflowsTest extends IntegrationTestCase
{
    public function testItShouldReturnEmptyListWhenNoWorkflows(): void
    {
        $this->listWorkflows();

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
        $this->createWorkflows(25);

        $this->listWorkflows('?page=1&limit=10');

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
        $this->createWorkflows(25);

        $this->listWorkflows('?page=2&limit=10');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(10, $response['items']);
        self::assertSame(2, $response['pagination']['page']);
        self::assertTrue($response['pagination']['hasNextPage']);
        self::assertTrue($response['pagination']['hasPreviousPage']);
    }

    public function testItShouldReturnLastPage(): void
    {
        $this->createWorkflows(25);

        $this->listWorkflows('?page=3&limit=10');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(5, $response['items']);
        self::assertFalse($response['pagination']['hasNextPage']);
        self::assertTrue($response['pagination']['hasPreviousPage']);
    }

    public function testItShouldFilterBySearchInName(): void
    {
        $workflow1 = Workflow::draft(
            DirectiveId::fromString('workflow-authentication'),
            'Authentication workflow',
            'Workflow for authentication',
        );
        $workflow2 = Workflow::draft(
            DirectiveId::fromString('workflow-authorization'),
            'Authorization workflow',
            'Workflow for authorization',
        );
        $workflow3 = Workflow::draft(
            DirectiveId::fromString('workflow-validation'),
            'Validation workflow',
            'Workflow for validation',
        );
        $this->persistEntity($workflow1);
        $this->persistEntity($workflow2);
        $this->persistEntity($workflow3);

        $this->listWorkflows('?search=Auth');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(2, $response['items']);
        self::assertSame(2, $response['pagination']['total']);
    }

    public function testItShouldFilterBySearchInDescription(): void
    {
        $workflow1 = Workflow::draft(
            DirectiveId::fromString('workflow-special'),
            'Workflow special',
            'Contains special keyword',
        );
        $workflow2 = Workflow::draft(
            DirectiveId::fromString('workflow-normal'),
            'Workflow normal',
            'Normal description',
        );
        $this->persistEntity($workflow1);
        $this->persistEntity($workflow2);

        $this->listWorkflows('?search=special');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(1, $response['items']);
        self::assertSame('workflow-special', $response['items'][0]['id']);
    }

    public function testItShouldFilterByState(): void
    {
        $workflow1 = self::draftWorkflowEntity('workflow-draft');
        $workflow2 = self::draftWorkflowEntity('workflow-published');
        $workflow2->publish();
        $workflow3 = self::draftWorkflowEntity('workflow-archived');
        $workflow3->archive();
        $this->persistEntity($workflow1);
        $this->persistEntity($workflow2);
        $this->persistEntity($workflow3);

        $this->listWorkflows('?state=draft');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(1, $response['items']);
        self::assertSame('workflow-draft', $response['items'][0]['id']);
    }

    public function testItShouldReturnPublishedWorkflows(): void
    {
        $workflow1 = self::draftWorkflowEntity('workflow-draft-one');
        $workflow2 = self::draftWorkflowEntity('workflow-published-one');
        $workflow2->publish();
        $this->persistEntity($workflow1);
        $this->persistEntity($workflow2);

        $this->listWorkflows('?state=published');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(1, $response['items']);
        self::assertSame('workflow-published-one', $response['items'][0]['id']);
    }

    public function testItShouldSortByNameAscending(): void
    {
        $workflow1 = Workflow::draft(
            DirectiveId::fromString('workflow-zebra'),
            'Zebra workflow',
            'Description',
        );
        $workflow2 = Workflow::draft(
            DirectiveId::fromString('workflow-alpha'),
            'Alpha workflow',
            'Description',
        );
        $this->persistEntity($workflow1);
        $this->persistEntity($workflow2);

        $this->listWorkflows('?sortBy=name&sortOrder=asc');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertSame('workflow-alpha', $response['items'][0]['id']);
        self::assertSame('workflow-zebra', $response['items'][1]['id']);
    }

    public function testItShouldSortByNameDescending(): void
    {
        $workflow1 = Workflow::draft(
            DirectiveId::fromString('workflow-alpha-desc'),
            'Alpha workflow',
            'Description',
        );
        $workflow2 = Workflow::draft(
            DirectiveId::fromString('workflow-zebra-desc'),
            'Zebra workflow',
            'Description',
        );
        $this->persistEntity($workflow1);
        $this->persistEntity($workflow2);

        $this->listWorkflows('?sortBy=name&sortOrder=desc');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertSame('workflow-zebra-desc', $response['items'][0]['id']);
        self::assertSame('workflow-alpha-desc', $response['items'][1]['id']);
    }

    public function testItShouldCombineFilterAndSort(): void
    {
        $workflow1 = Workflow::draft(
            DirectiveId::fromString('workflow-zebra-auth'),
            'Zebra authentication',
            'Description',
        );
        $workflow2 = Workflow::draft(
            DirectiveId::fromString('workflow-alpha-auth'),
            'Alpha authentication',
            'Description',
        );
        $workflow3 = Workflow::draft(
            DirectiveId::fromString('workflow-validation-only'),
            'Validation workflow',
            'Description',
        );
        $this->persistEntity($workflow1);
        $this->persistEntity($workflow2);
        $this->persistEntity($workflow3);

        $this->listWorkflows('?search=authentication&sortBy=name&sortOrder=asc');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(2, $response['items']);
        self::assertSame('workflow-alpha-auth', $response['items'][0]['id']);
        self::assertSame('workflow-zebra-auth', $response['items'][1]['id']);
    }

    public function testItShouldExcludeSoftDeletedWorkflows(): void
    {
        $workflow1 = self::draftWorkflowEntity('workflow-active');
        $workflow2 = self::draftWorkflowEntity('workflow-deleted');
        $workflow2->delete();
        $this->persistEntity($workflow1);
        $this->persistEntity($workflow2);

        $this->listWorkflows();

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertCount(1, $response['items']);
        self::assertSame('workflow-active', $response['items'][0]['id']);
    }

    public function testItShouldEnforceLimitMax(): void
    {
        $this->createWorkflows(5);

        $this->listWorkflows('?limit=200');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertSame(100, $response['pagination']['limit']);
    }

    public function testItShouldEnforcePageMin(): void
    {
        $this->createWorkflows(5);

        $this->listWorkflows('?page=0');

        self::assertResponseIsSuccessful();

        $response = $this->getJsonResponse();
        self::assertSame(1, $response['pagination']['page']);
    }

    private function listWorkflows(string $query = ''): void
    {
        $this->getJson(\sprintf('/api/authoring/workflows%s', $query));
    }

    private function createWorkflows(int $count): void
    {
        $names = ['alpha', 'bravo', 'charlie', 'delta', 'echo', 'foxtrot', 'golf', 'hotel', 'india', 'juliet',
            'kilo', 'lima', 'mike', 'november', 'oscar', 'papa', 'quebec', 'romeo', 'sierra', 'tango',
            'uniform', 'victor', 'whiskey', 'xray', 'yankee', 'zulu'];

        for ($i = 0; $i < $count; ++$i) {
            $baseName = $names[$i % \count($names)];
            $suffix = (int) floor($i / \count($names));
            $id = $suffix > 0 ? \sprintf('workflow-%s-%s', $baseName, $this->numberToWord($suffix)) : \sprintf('workflow-%s', $baseName);

            $workflow = Workflow::draft(
                DirectiveId::fromString($id),
                \sprintf('Workflow %s', $baseName),
                \sprintf('Description for workflow %s', $baseName),
            );
            $this->persistEntity($workflow);
        }
    }

    private function numberToWord(int $number): string
    {
        $words = ['one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten'];

        return $words[($number - 1) % \count($words)] ?? 'extra';
    }

    /**
     * @return array{items: list<array{id: string, name: string, description: string, content: string|null, state: string, createdAt: string, updatedAt: string, examples: list<mixed>, steps: list<mixed>}>, pagination: array{page: int, limit: int, total: int, totalPages: int, hasNextPage: bool, hasPreviousPage: bool}}
     */
    private function getJsonResponse(): array
    {
        $response = self::getClient()?->getResponse();
        self::assertInstanceOf(Response::class, $response);

        $content = $response->getContent();
        self::assertIsString($content);

        /** @var array{items: list<array{id: string, name: string, description: string, content: string|null, state: string, createdAt: string, updatedAt: string, examples: list<mixed>, steps: list<mixed>}>, pagination: array{page: int, limit: int, total: int, totalPages: int, hasNextPage: bool, hasPreviousPage: bool}} */
        return \Safe\json_decode($content, true);
    }
}
