<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Rule\Rule;
use Dairectiv\Authoring\Domain\Rule\RuleContent;
use Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Repository\DoctrineDirectiveRepository;
use Dairectiv\Tests\Framework\IntegrationTestCase;

final class DoctrineDirectiveRepositoryTest extends IntegrationTestCase
{
    private DoctrineDirectiveRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getService(DoctrineDirectiveRepository::class);
    }

    public function testItShouldSaveAndFindDirectiveById(): void
    {
        $id = DirectiveId::fromString('test-rule');
        $rule = $this->createRule($id);

        $this->repository->save($rule);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $found = $this->repository->findDirectiveById($id);

        self::assertNotNull($found);
        self::assertInstanceOf(Rule::class, $found);
        self::assertTrue($id->id === $found->id->id);
    }

    public function testItShouldSaveAndGetDirectiveById(): void
    {
        $id = DirectiveId::fromString('test-rule-get');
        $rule = $this->createRule($id);

        $this->repository->save($rule);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $found = $this->repository->getDirectiveById($id);

        self::assertInstanceOf(Rule::class, $found);
        self::assertTrue($id->id === $found->id->id);
    }

    public function testFindDirectiveByIdShouldReturnNullWhenNotFound(): void
    {
        $id = DirectiveId::fromString('non-existent-rule');

        $found = $this->repository->findDirectiveById($id);

        self::assertNull($found);
    }

    public function testGetDirectiveByIdShouldThrowExceptionWhenNotFound(): void
    {
        $id = DirectiveId::fromString('non-existent-rule');

        $this->expectException(DirectiveNotFoundException::class);
        $this->expectExceptionMessage('Directive with ID non-existent-rule not found.');

        $this->repository->getDirectiveById($id);
    }

    private function createRule(DirectiveId $id): Rule
    {
        return Rule::draft(
            $id,
            DirectiveMetadata::create(
                DirectiveName::fromString('Test Rule'),
                DirectiveDescription::fromString('A test rule description'),
            ),
            RuleContent::fromString('Always write tests'),
        );
    }
}
