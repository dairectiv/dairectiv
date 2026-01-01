<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Rule\RuleSearchCriteria;
use Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Repository\DoctrineRuleRepository;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-repository')]
final class DoctrineRuleRepositoryTest extends IntegrationTestCase
{
    private DoctrineRuleRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getService(DoctrineRuleRepository::class);
    }

    public function testItShouldSaveAndFindRuleById(): void
    {
        $rule = self::draftRuleEntity();

        $this->repository->save($rule);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $found = $this->findEntity(Rule::class, ['id' => $rule->id]);

        self::assertNotNull($found);
        self::assertTrue($rule->id->equals($found->id));
    }

    public function testItShouldSaveAndGetRuleById(): void
    {
        $rule = self::draftRuleEntity();

        $this->repository->save($rule);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $found = $this->repository->getRuleById($rule->id);

        self::assertTrue($rule->id->equals($found->id));
    }

    public function testItShouldThrowExceptionWhenRuleNotFound(): void
    {
        $id = DirectiveId::fromString('non-existent-rule');

        $this->expectException(RuleNotFoundException::class);
        $this->expectExceptionMessage('Rule with ID non-existent-rule not found.');

        $this->repository->getRuleById($id);
    }

    public function testItShouldSearchByCriteriaWithEmptyResult(): void
    {
        $criteria = new RuleSearchCriteria();

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertSame([], $result);
    }

    public function testItShouldSearchByCriteriaWithPagination(): void
    {
        $this->createRules(15);
        $criteria = new RuleSearchCriteria();

        $firstPage = $this->repository->searchByCriteria($criteria, 0, 10);
        $secondPage = $this->repository->searchByCriteria($criteria, 10, 10);

        self::assertCount(10, $firstPage);
        self::assertCount(5, $secondPage);
    }

    public function testItShouldSearchByCriteriaWithSearchOnName(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-alpha', name: 'Alpha Rule'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-beta', name: 'Beta Rule'));
        $this->getEntityManager()->clear();

        $criteria = new RuleSearchCriteria(search: 'Alpha');

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(1, $result);
        self::assertSame('rule-alpha', (string) $result[0]->id);
    }

    public function testItShouldSearchByCriteriaWithSearchOnDescription(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-one', name: 'Rule One', description: 'Contains keyword'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-two', name: 'Rule Two', description: 'Different text'));
        $this->getEntityManager()->clear();

        $criteria = new RuleSearchCriteria(search: 'keyword');

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(1, $result);
        self::assertSame('rule-one', (string) $result[0]->id);
    }

    public function testItShouldSearchByCriteriaWithStateFilter(): void
    {
        $draftRule = self::draftRuleEntity(id: 'draft-rule');
        $this->persistEntity($draftRule);

        $publishedRule = self::draftRuleEntity(id: 'published-rule');
        $publishedRule->publish();
        $this->persistEntity($publishedRule);
        $this->getEntityManager()->clear();

        $criteria = new RuleSearchCriteria(state: DirectiveState::Published);

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(1, $result);
        self::assertSame('published-rule', (string) $result[0]->id);
    }

    public function testItShouldSearchByCriteriaWithSortByName(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-charlie', name: 'Charlie'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-alpha', name: 'Alpha'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-bravo', name: 'Bravo'));
        $this->getEntityManager()->clear();

        $criteria = new RuleSearchCriteria(sortBy: 'name', sortOrder: 'asc');

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(3, $result);
        self::assertSame('rule-alpha', (string) $result[0]->id);
        self::assertSame('rule-bravo', (string) $result[1]->id);
        self::assertSame('rule-charlie', (string) $result[2]->id);
    }

    public function testItShouldSearchByCriteriaWithSortByUpdatedAt(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-first'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-second'));
        $this->getEntityManager()->clear();

        $criteria = new RuleSearchCriteria(sortBy: 'updatedAt', sortOrder: 'desc');

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(2, $result);
    }

    public function testItShouldSearchByCriteriaWithCombinedFilters(): void
    {
        $draftAlpha = self::draftRuleEntity(id: 'draft-alpha', name: 'Alpha Draft');
        $this->persistEntity($draftAlpha);

        $publishedAlpha = self::draftRuleEntity(id: 'published-alpha', name: 'Alpha Published');
        $publishedAlpha->publish();
        $this->persistEntity($publishedAlpha);

        $draftBeta = self::draftRuleEntity(id: 'draft-beta', name: 'Beta Draft');
        $this->persistEntity($draftBeta);
        $this->getEntityManager()->clear();

        $criteria = new RuleSearchCriteria(search: 'Alpha', state: DirectiveState::Draft);

        $result = $this->repository->searchByCriteria($criteria, 0, 20);

        self::assertCount(1, $result);
        self::assertSame('draft-alpha', (string) $result[0]->id);
    }

    public function testItShouldCountByCriteriaWithEmptyResult(): void
    {
        $criteria = new RuleSearchCriteria();

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(0, $count);
    }

    public function testItShouldCountByCriteriaWithAllRules(): void
    {
        $this->createRules(5);
        $criteria = new RuleSearchCriteria();

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(5, $count);
    }

    public function testItShouldCountByCriteriaWithSearchFilter(): void
    {
        $this->persistEntity(self::draftRuleEntity(id: 'rule-alpha', name: 'Alpha Rule'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-beta', name: 'Beta Rule'));
        $this->persistEntity(self::draftRuleEntity(id: 'rule-gamma', name: 'Alpha Gamma'));
        $this->getEntityManager()->clear();

        $criteria = new RuleSearchCriteria(search: 'Alpha');

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(2, $count);
    }

    public function testItShouldCountByCriteriaWithStateFilter(): void
    {
        $draftRule = self::draftRuleEntity(id: 'draft-rule');
        $this->persistEntity($draftRule);

        $publishedRule1 = self::draftRuleEntity(id: 'published-rule-one');
        $publishedRule1->publish();
        $this->persistEntity($publishedRule1);

        $publishedRule2 = self::draftRuleEntity(id: 'published-rule-two');
        $publishedRule2->publish();
        $this->persistEntity($publishedRule2);
        $this->getEntityManager()->clear();

        $criteria = new RuleSearchCriteria(state: DirectiveState::Published);

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(2, $count);
    }

    public function testItShouldCountByCriteriaWithCombinedFilters(): void
    {
        $draftAlpha = self::draftRuleEntity(id: 'draft-alpha', name: 'Alpha Draft');
        $this->persistEntity($draftAlpha);

        $publishedAlpha = self::draftRuleEntity(id: 'published-alpha', name: 'Alpha Published');
        $publishedAlpha->publish();
        $this->persistEntity($publishedAlpha);

        $draftBeta = self::draftRuleEntity(id: 'draft-beta', name: 'Beta Draft');
        $this->persistEntity($draftBeta);
        $this->getEntityManager()->clear();

        $criteria = new RuleSearchCriteria(search: 'Alpha', state: DirectiveState::Draft);

        $count = $this->repository->countByCriteria($criteria);

        self::assertSame(1, $count);
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
        $this->getEntityManager()->clear();
    }
}
