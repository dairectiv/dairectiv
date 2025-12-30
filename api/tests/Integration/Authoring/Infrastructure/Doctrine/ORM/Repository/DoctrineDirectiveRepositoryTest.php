<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\ORM\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Infrastructure\Doctrine\ORM\Repository\DoctrineDirectiveRepository;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-repository')]
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
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $found = $this->repository->findDirectiveById($rule->id);

        self::assertNotNull($found);
        self::assertInstanceOf(Rule::class, $found);
        self::assertTrue($rule->id->equals($found->id));
    }

    public function testItShouldSaveAndGetDirectiveById(): void
    {
        $rule = self::draftRule();
        $this->persistEntity($rule);

        $found = $this->repository->getDirectiveById($rule->id);

        self::assertInstanceOf(Rule::class, $found);
        self::assertTrue($rule->id->equals($found->id));
    }

    public function testItShouldReturnNullWhenDirectiveNotFound(): void
    {
        $id = DirectiveId::fromString('non-existent-rule');

        $found = $this->repository->findDirectiveById($id);

        self::assertNull($found);
    }

    public function testItShouldThrowExceptionWhenDirectiveNotFound(): void
    {
        $id = DirectiveId::fromString('non-existent-rule');

        $this->expectException(DirectiveNotFoundException::class);
        $this->expectExceptionMessage('Directive with ID non-existent-rule not found.');

        $this->repository->getDirectiveById($id);
    }
}
