---
name: repository
description: Guide for implementing repositories following Port & Adapters pattern. Use when creating, modifying, or adding methods to repository interfaces or implementations.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# Repository Implementation Guide

This Skill provides patterns for implementing repositories following the Port & Adapters (Hexagonal) architecture.

## When to Use

- Creating a new repository for an Aggregate Root
- Adding methods to an existing repository interface
- Implementing repository methods in Doctrine
- Writing integration tests for repository queries

## Directory Structure

```
src/{BoundedContext}/
├── Domain/Repository/
│   └── {Entity}Repository.php          # Interface (Port)
└── Infrastructure/Doctrine/ORM/Repository/
    └── Doctrine{Entity}Repository.php  # Implementation (Adapter)

tests/Integration/{BoundedContext}/Infrastructure/Doctrine/ORM/Repository/
    └── Doctrine{Entity}RepositoryTest.php
```

## Method Naming Conventions

PHPStan enforces these naming rules via `RepositoryMethodRule`:

| Prefix   | Return Type          | Exception                         | Purpose                          |
|----------|----------------------|-----------------------------------|----------------------------------|
| `get`    | Non-nullable         | `@throws EntityNotFoundException` | Single entity, fail if not found |
| `find`   | Nullable (`?Entity`) | None                              | Single entity, null if not found |
| `count`  | `int`                | None                              | Count matching entities          |
| `search` | `array`              | None                              | Multiple entities                |

## Interface Pattern (Domain Layer)

```php
namespace Dairectiv\{Context}\Domain\Repository;

use Dairectiv\{Context}\Domain\Object\{Entity}\Exception\{Entity}NotFoundException;

interface {Entity}Repository
{
    public function save({Entity} $entity): void;

    /**
     * @throws {Entity}NotFoundException
     */
    public function get{Entity}ById({Entity}Id $id): {Entity};

    public function find{Entity}ById({Entity}Id $id): ?{Entity};

    /**
     * @return list<{Entity}>
     */
    public function searchByCriteria({Entity}SearchCriteria $criteria, int $offset, int $limit): array;

    public function countByCriteria({Entity}SearchCriteria $criteria): int;
}
```

## Implementation Pattern (Infrastructure Layer)

```php
namespace Dairectiv\{Context}\Infrastructure\Doctrine\ORM\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<{Entity}>
 */
final class Doctrine{Entity}Repository extends ServiceEntityRepository implements {Entity}Repository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, {Entity}::class);
    }

    public function save({Entity} $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function get{Entity}ById({Entity}Id $id): {Entity}
    {
        $entity = $this->find($id);

        if (null === $entity) {
            throw {Entity}NotFoundException::fromId($id);
        }

        return $entity;
    }
}
```

## Testing Pattern

```php
#[Group('integration')]
#[Group('{bounded-context}')]
#[Group('doctrine-repository')]
final class Doctrine{Entity}RepositoryTest extends IntegrationTestCase
{
    private Doctrine{Entity}Repository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = self::getService(Doctrine{Entity}Repository::class);
    }

    public function testItShouldSaveAndGetById(): void
    {
        $entity = self::createEntity();

        $this->repository->save($entity);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $found = $this->repository->get{Entity}ById($entity->id);

        self::assertTrue($entity->id->equals($found->id));
    }

    public function testItShouldThrowExceptionWhenNotFound(): void
    {
        $id = {Entity}Id::fromString('non-existent');

        $this->expectException({Entity}NotFoundException::class);

        $this->repository->get{Entity}ById($id);
    }
}
```

## Key Rules

1. **Interface in Domain, Implementation in Infrastructure** - Never put Doctrine code in Domain layer
2. **One repository per Aggregate Root** - Repositories are for aggregate roots only, not child entities
3. **No flush() in repository** - Let the caller control transaction boundaries
4. **Use QueryBuilder for complex queries** - Extract criteria application to private methods
5. **Always test with real database** - Integration tests, not mocks

## Reference Files

- `api/src/Authoring/Domain/Repository/RuleRepository.php`
- `api/src/Authoring/Infrastructure/Doctrine/ORM/Repository/DoctrineRuleRepository.php`
- `api/tests/Integration/Authoring/Infrastructure/Doctrine/ORM/Repository/DoctrineRuleRepositoryTest.php`
- `api/tools/phpstan/src/Rules/RepositoryMethodRule.php`