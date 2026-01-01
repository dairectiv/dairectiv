<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Rule;

use Dairectiv\Authoring\Application\Rule\ListRules\Input;
use Dairectiv\Authoring\Application\Rule\ListRules\Output;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('use-case')]
final class ListTest extends IntegrationTestCase
{
    public function testItShouldReturnEmptyListWhenNoRulesExist(): void
    {
        $output = $this->executeListRules();

        self::assertSame([], $output->items);
        self::assertSame(0, $output->total);
        self::assertSame(1, $output->page);
        self::assertSame(20, $output->limit);
        self::assertSame(0, $output->totalPages());
        self::assertFalse($output->hasNextPage());
        self::assertFalse($output->hasPreviousPage());
    }

    public function testItShouldReturnAllRulesWithDefaultPagination(): void
    {
        $this->createRules(5);

        $output = $this->executeListRules();

        self::assertCount(5, $output->items);
        self::assertSame(5, $output->total);
        self::assertSame(1, $output->page);
        self::assertSame(20, $output->limit);
        self::assertSame(1, $output->totalPages());
        self::assertFalse($output->hasNextPage());
        self::assertFalse($output->hasPreviousPage());
    }

    public function testItShouldPaginateResults(): void
    {
        $this->createRules(25);

        $output = $this->executeListRules(page: 1, limit: 10);

        self::assertCount(10, $output->items);
        self::assertSame(25, $output->total);
        self::assertSame(1, $output->page);
        self::assertSame(10, $output->limit);
        self::assertSame(3, $output->totalPages());
        self::assertTrue($output->hasNextPage());
        self::assertFalse($output->hasPreviousPage());

        $output = $this->executeListRules(page: 2, limit: 10);

        self::assertCount(10, $output->items);
        self::assertSame(2, $output->page);
        self::assertTrue($output->hasNextPage());
        self::assertTrue($output->hasPreviousPage());

        $output = $this->executeListRules(page: 3, limit: 10);

        self::assertCount(5, $output->items);
        self::assertSame(3, $output->page);
        self::assertFalse($output->hasNextPage());
        self::assertTrue($output->hasPreviousPage());
    }

    public function testItShouldSearchByName(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-alpha', name: 'Alpha Rule'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-beta', name: 'Beta Rule'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-gamma', name: 'Gamma Rule'));

        $output = $this->executeListRules(search: 'Alpha');

        self::assertCount(1, $output->items);
        self::assertSame('rule-alpha', (string) $output->items[0]->id);
    }

    public function testItShouldSearchByDescription(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-one', name: 'Rule One', description: 'Description with keyword'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-two', name: 'Rule Two', description: 'Another description'));

        $output = $this->executeListRules(search: 'keyword');

        self::assertCount(1, $output->items);
        self::assertSame('rule-one', (string) $output->items[0]->id);
    }

    public function testItShouldSearchCaseInsensitive(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-upper', name: 'UPPERCASE Rule'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-lower', name: 'lowercase rule'));

        $output = $this->executeListRules(search: 'uppercase');

        self::assertCount(1, $output->items);
        self::assertSame('rule-upper', (string) $output->items[0]->id);
    }

    public function testItShouldFilterByDraftState(): void
    {
        $draftRule = self::draftRuleEntity(id: 'draft-rule');
        $this->persistEntity($draftRule);

        $publishedRule = self::draftRuleEntity(id: 'published-rule');
        $publishedRule->publish();
        $this->persistEntity($publishedRule);

        $output = $this->executeListRules(state: 'draft');

        self::assertCount(1, $output->items);
        self::assertSame('draft-rule', (string) $output->items[0]->id);
    }

    public function testItShouldFilterByPublishedState(): void
    {
        $draftRule = self::draftRuleEntity(id: 'draft-rule');
        $this->persistEntity($draftRule);

        $publishedRule = self::draftRuleEntity(id: 'published-rule');
        $publishedRule->publish();
        $this->persistEntity($publishedRule);

        $output = $this->executeListRules(state: 'published');

        self::assertCount(1, $output->items);
        self::assertSame('published-rule', (string) $output->items[0]->id);
    }

    public function testItShouldFilterByArchivedState(): void
    {
        $draftRule = self::draftRuleEntity(id: 'draft-rule');
        $this->persistEntity($draftRule);

        $archivedRule = self::draftRuleEntity(id: 'archived-rule');
        $archivedRule->publish();
        $archivedRule->archive();
        $this->persistEntity($archivedRule);

        $output = $this->executeListRules(state: 'archived');

        self::assertCount(1, $output->items);
        // archive() appends a UUID suffix to the ID
        self::assertStringStartsWith('archived-rule-', (string) $output->items[0]->id);
    }

    public function testItShouldSortByNameAscending(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-charlie', name: 'Charlie'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-alpha', name: 'Alpha'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-bravo', name: 'Bravo'));

        $output = $this->executeListRules(sortBy: 'name', sortOrder: 'asc');

        self::assertCount(3, $output->items);
        self::assertSame('rule-alpha', (string) $output->items[0]->id);
        self::assertSame('rule-bravo', (string) $output->items[1]->id);
        self::assertSame('rule-charlie', (string) $output->items[2]->id);
    }

    public function testItShouldSortByNameDescending(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-alpha', name: 'Alpha'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-charlie', name: 'Charlie'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-bravo', name: 'Bravo'));

        $output = $this->executeListRules(sortBy: 'name', sortOrder: 'desc');

        self::assertCount(3, $output->items);
        self::assertSame('rule-charlie', (string) $output->items[0]->id);
        self::assertSame('rule-bravo', (string) $output->items[1]->id);
        self::assertSame('rule-alpha', (string) $output->items[2]->id);
    }

    public function testItShouldSortByCreatedAt(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-first', name: 'First'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-second', name: 'Second'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-third', name: 'Third'));

        $output = $this->executeListRules(sortBy: 'createdAt', sortOrder: 'asc');

        self::assertCount(3, $output->items);
        self::assertSame('rule-first', (string) $output->items[0]->id);
        self::assertSame('rule-second', (string) $output->items[1]->id);
        self::assertSame('rule-third', (string) $output->items[2]->id);
    }

    public function testItShouldCombineSearchAndStateFilter(): void
    {
        $draftAlpha = self::draftRuleEntity(id: 'draft-alpha', name: 'Alpha Draft');
        $this->persistEntity($draftAlpha);

        $publishedAlpha = self::draftRuleEntity(id: 'published-alpha', name: 'Alpha Published');
        $publishedAlpha->publish();
        $this->persistEntity($publishedAlpha);

        $draftBeta = self::draftRuleEntity(id: 'draft-beta', name: 'Beta Draft');
        $this->persistEntity($draftBeta);

        $output = $this->executeListRules(search: 'Alpha', state: 'draft');

        self::assertCount(1, $output->items);
        self::assertSame('draft-alpha', (string) $output->items[0]->id);
    }

    public function testItShouldExcludeDeletedRules(): void
    {
        $activeRule = self::draftRuleEntity(id: 'active-rule');
        $this->persistEntity($activeRule);

        $deletedRule = self::draftRuleEntity(id: 'deleted-rule');
        $deletedRule->delete();
        $this->persistEntity($deletedRule);

        $output = $this->executeListRules();

        self::assertCount(1, $output->items);
        self::assertSame('active-rule', (string) $output->items[0]->id);
    }

    public function testItShouldIgnoreInvalidStateFilter(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-one'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-two'));

        $output = $this->executeListRules(state: 'invalid-state');

        self::assertCount(2, $output->items);
    }

    public function testItShouldReturnRulesWithAllProperties(): void
    {
        $rule = self::draftRuleEntity(id: 'full-rule', name: 'Full Rule', description: 'Full description');
        $rule->updateContent('Full content');
        $this->persistEntity($rule);

        $output = $this->executeListRules();

        self::assertCount(1, $output->items);
        $returnedRule = $output->items[0];

        self::assertSame('full-rule', (string) $returnedRule->id);
        self::assertSame('Full Rule', $returnedRule->name);
        self::assertSame('Full description', $returnedRule->description);
        self::assertSame('Full content', $returnedRule->content);
        self::assertSame(DirectiveState::Draft, $returnedRule->state);
    }

    private const array NAMES = [
        'alpha', 'bravo', 'charlie', 'delta', 'echo', 'foxtrot', 'golf', 'hotel',
        'india', 'juliet', 'kilo', 'lima', 'mike', 'november', 'oscar', 'papa',
        'quebec', 'romeo', 'sierra', 'tango', 'uniform', 'victor', 'whiskey', 'xray',
        'yankee', 'zulu',
    ];

    /**
     * @param int<1, max> $count
     */
    private function createRules(int $count): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $name = self::NAMES[$i % 26];
            $suffix = $i >= 26 ? \sprintf('-%d', (int) ($i / 26)) : '';
            $this->persistEntity(self::draftRuleEntity(
                id: \sprintf('rule-%s%s', $name, $suffix),
                name: \sprintf('Rule %s%s', ucfirst($name), $suffix),
            ));
        }
    }

    private function executeListRules(
        int $page = 1,
        int $limit = 20,
        ?string $search = null,
        ?string $state = null,
        string $sortBy = 'createdAt',
        string $sortOrder = 'desc',
    ): Output {
        $output = $this->fetch(new Input(
            page: $page,
            limit: $limit,
            search: $search,
            state: $state,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
        ));

        self::assertInstanceOf(Output::class, $output);

        return $output;
    }
}
