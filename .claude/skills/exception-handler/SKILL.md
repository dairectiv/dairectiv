---
name: exception-handler
description: Guide for exception handling patterns in the domain layer. Use when creating exceptions, handling errors in controllers, or testing exception scenarios.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# Exception Handling Guide

This Skill provides patterns for implementing and handling exceptions.

## When to Use

- Creating entity-specific exceptions
- Handling domain exceptions in controllers
- Testing exception scenarios
- Understanding HTTP status code mapping

## Exception Hierarchy

```
DomainException (abstract)
├── EntityNotFoundException (abstract) → HTTP 404
│   ├── RuleNotFoundException
│   ├── WorkflowNotFoundException
│   └── DirectiveNotFoundException
├── InvalidArgumentException → HTTP 422
└── {Entity}ConflictException → HTTP 409
```

## Base Exceptions

### DomainException

Abstract base for all domain-level exceptions:

```php
namespace Dairectiv\SharedKernel\Domain\Object\Exception;

abstract class DomainException extends \DomainException
{
}
```

### EntityNotFoundException

For "entity not found" scenarios. Used in repository `get*` methods:

```php
abstract class EntityNotFoundException extends DomainException
{
}
```

### InvalidArgumentException

Thrown by `Assert` class for validation failures:

```php
final class InvalidArgumentException extends DomainException
{
}
```

## Creating Entity-Specific Exceptions

### NotFoundException Pattern

```php
namespace Dairectiv\{Context}\Domain\Object\{Entity}\Exception;

use Dairectiv\SharedKernel\Domain\Object\Exception\EntityNotFoundException;

final class {Entity}NotFoundException extends EntityNotFoundException
{
    public static function fromId({Entity}Id $id): self
    {
        return new self(\sprintf('{Entity} with ID %s not found.', $id));
    }
}
```

### ConflictException Pattern

For optimistic locking or uniqueness violations:

```php
final class {Entity}AlreadyExistsException extends DomainException
{
    public static function fromId({Entity}Id $id): self
    {
        return new self(\sprintf('{Entity} with ID "%s" already exists.', $id));
    }
}
```

## Directory Structure

```
src/{BoundedContext}/Domain/Object/{Entity}/Exception/
├── {Entity}NotFoundException.php
└── {Entity}AlreadyExistsException.php
```

## HTTP Status Code Mapping

Symfony automatically maps exceptions to HTTP responses:

| Exception Type              | HTTP Status       | When to Use                      |
|-----------------------------|-------------------|----------------------------------|
| `EntityNotFoundException`   | 404 Not Found     | Entity doesn't exist             |
| `InvalidArgumentException`  | 422 Unprocessable | Validation failed                |
| `{Entity}ConflictException` | 409 Conflict      | Already exists, version mismatch |

## Usage in Repository

```php
public function getRuleById(DirectiveId $id): Rule
{
    $rule = $this->find($id);

    if (null === $rule) {
        throw RuleNotFoundException::fromId($id);
    }

    return $rule;
}
```

## Usage in Use Cases

```php
public function __invoke(CreateRuleCommand $command): void
{
    $existingRule = $this->repository->findRuleById($command->id);

    if (null !== $existingRule) {
        throw DirectiveAlreadyExistsException::fromId($command->id);
    }

    // Create rule...
}
```

## Testing Exceptions

### Unit Test

```php
public function testItShouldThrowWhenPublishingAlreadyPublished(): void
{
    $rule = Rule::draft($id, 'Name', 'Desc');
    $rule->publish();
    $this->resetDomainEvents();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Only draft directives can be published.');

    $rule->publish();
}
```

### Integration Test (Repository)

```php
public function testItShouldThrowExceptionWhenRuleNotFound(): void
{
    $id = DirectiveId::fromString('non-existent-rule');

    $this->expectException(RuleNotFoundException::class);
    $this->expectExceptionMessage('Rule with ID non-existent-rule not found.');

    $this->repository->getRuleById($id);
}
```

### Integration Test (API)

```php
public function testItShouldReturn404WhenRuleNotFound(): void
{
    $this->getJson('/api/authoring/rules/non-existent');

    self::assertResponseStatusCodeSame(404);
}

public function testItShouldReturn409WhenRuleAlreadyExists(): void
{
    $this->createRule('existing-rule');

    $this->postJson('/api/authoring/rules', ['id' => 'existing-rule', ...]);

    self::assertResponseStatusCodeSame(409);
}
```

## Checklist

When creating an exception:
- [ ] Extends appropriate base exception (`EntityNotFoundException`, `DomainException`)
- [ ] Uses `final class`
- [ ] Has `fromId()` or similar factory method
- [ ] Message includes relevant identifier
- [ ] Located in `{Entity}/Exception/` directory

When using exceptions:
- [ ] Use `get*` methods for mandatory entities (throw on not found)
- [ ] Use `find*` methods for optional entities (return null)
- [ ] Test both success and exception paths

## Reference Files

- `api/src/SharedKernel/Domain/Object/Exception/DomainException.php`
- `api/src/SharedKernel/Domain/Object/Exception/EntityNotFoundException.php`
- `api/src/SharedKernel/Domain/Object/Exception/InvalidArgumentException.php`
- `api/src/Authoring/Domain/Object/Rule/Exception/RuleNotFoundException.php`