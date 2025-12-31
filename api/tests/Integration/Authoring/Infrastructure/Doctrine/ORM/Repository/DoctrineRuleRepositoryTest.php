<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
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
}
