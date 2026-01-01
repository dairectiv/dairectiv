---
name: aggregate-root
description: Guide for implementing DDD Aggregate Roots with rich domain models, validation, domain events, and exhaustive tests. Use when creating or modifying aggregates.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# Aggregate Root Implementation Guide

This Skill provides best practices for implementing DDD Aggregate Roots with rich domain models.

## When to Use

- Creating a new Aggregate Root
- Adding behavior to an existing aggregate
- Implementing state transitions
- Writing exhaustive unit tests for domain logic

## Core Principles

### 1. Rich Domain Model (Not Anemic)

Aggregates must encapsulate business logic, not just hold data:

```php
// BAD: Anemic model with public setters
$rule->setContent($content);
$rule->setState(DirectiveState::Published);

// GOOD: Rich model with behavior
$rule->updateContent($content);
$rule->publish();
```

### 2. Business Intent Through Method Names

Methods should express what happens in business terms:

| Bad             | Good                                 |
|-----------------|--------------------------------------|
| `setState()`    | `publish()`, `archive()`, `delete()` |
| `setContent()`  | `updateContent()`                    |
| `setExamples()` | `addExample()`, `removeExample()`    |

### 3. Protect Invariants

Validate state transitions and business rules:

```php
public function publish(): void
{
    Assert::eq($this->state, DirectiveState::Draft, 'Only draft directives can be published.');

    $this->state = DirectiveState::Published;
    $this->recordEvent(new DirectivePublished($this->id));
}
```

### 4. Domain Events for State Changes

Record events for significant business actions:

```php
protected function initialize(DirectiveId $id, string $name): void
{
    $this->id = $id;
    $this->name = $name;
    $this->state = DirectiveState::Draft;

    $this->recordEvent(new DirectiveDrafted($this->id));
}

public function archive(): void
{
    Assert::notEq($this->state, DirectiveState::Archived, 'Already archived.');

    $this->state = DirectiveState::Archived;
    $this->recordEvent(new DirectiveArchived($this->id));
}
```

## Directory Structure

```
src/{BoundedContext}/Domain/Object/{Aggregate}/
├── {Aggregate}.php           # Aggregate root
├── {Aggregate}Id.php         # Identity value object (optional)
├── Event/
│   ├── {Aggregate}Created.php
│   ├── {Aggregate}Updated.php
│   └── {Aggregate}Archived.php
└── Exception/
    └── {Aggregate}NotFoundException.php
```

## Implementation Patterns

### Factory Method (Named Constructor)

```php
public static function draft(DirectiveId $id, string $name, string $description): static
{
    $entity = new self();
    $entity->initialize($id, $name, $description);
    return $entity;
}
```

### State Guard Methods

```php
final protected function assertNotArchived(): void
{
    Assert::notEq($this->state, DirectiveState::Archived, 'Cannot modify archived entity.');
    Assert::notEq($this->state, DirectiveState::Deleted, 'Cannot modify deleted entity.');
}
```

### Mark Updated Pattern

```php
final public function markAsUpdated(): void
{
    $this->assertNotArchived();
    $this->updatedAt = Chronos::now();
    $this->recordEvent(new DirectiveUpdated($this->id));
}
```

## Testing Best Practices

### Use UnitTestCase with AggregateRootAssertions

```php
#[Group('unit')]
#[Group('{bounded-context}')]
final class {Aggregate}Test extends UnitTestCase
```

### Test Categories (Be Exhaustive)

1. **Creation/Lifecycle**
   - Happy path creation
   - All state transitions (draft → published → archived)

2. **Invalid State Transitions**
   - Cannot publish already published
   - Cannot archive already archived
   - Cannot modify deleted entity

3. **Behavior Methods**
   - Each method with valid inputs
   - Each method with invalid inputs
   - Edge cases (null, empty, boundary values)

4. **Domain Events**
   - Assert every event is recorded
   - Use `resetDomainEvents()` between actions

### Test Pattern

```php
public function testItShouldPublishRule(): void
{
    // Arrange
    $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'Name', 'Desc');
    $this->resetDomainEvents();

    // Act
    $rule->publish();

    // Assert state
    self::assertSame(DirectiveState::Published, $rule->state);

    // Assert event
    $this->assertDomainEventRecorded(DirectivePublished::class);
}

public function testItShouldNotPublishAlreadyPublished(): void
{
    $rule = Rule::draft(DirectiveId::fromString('my-rule'), 'Name', 'Desc');
    $rule->publish();
    $this->resetDomainEvents();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Only draft directives can be published.');

    $rule->publish();
}
```

### Domain Event Assertions

```php
// Assert specific event was recorded
$this->assertDomainEventRecorded(DirectiveCreated::class);

// Assert event recorded N times
$this->assertDomainEventRecorded(DirectiveUpdated::class, 3);

// Reset events mid-test
$this->resetDomainEvents();

// Assert no events (automatic in tearDown)
$this->assertNoDomainEvents();
```

## Checklist

When implementing an Aggregate Root:

- [ ] Uses factory method (`draft()`, `create()`) not public constructor
- [ ] Method names reflect business intent
- [ ] State transitions are guarded with Assert
- [ ] Domain events recorded for significant changes
- [ ] No public setters, only behavior methods
- [ ] `markAsUpdated()` called for modifications
- [ ] Extends `AggregateRoot` base class

When testing:

- [ ] Test all creation paths
- [ ] Test all valid state transitions
- [ ] Test all **invalid** state transitions
- [ ] Test each behavior method
- [ ] Assert all domain events
- [ ] Use `resetDomainEvents()` to isolate assertions
- [ ] Use `#[Group('unit')]` and bounded context group

## Reference Files

- `api/src/Authoring/Domain/Object/Directive/Directive.php` - Base aggregate
- `api/src/Authoring/Domain/Object/Rule/Rule.php` - Concrete aggregate
- `api/tests/Unit/Authoring/Domain/Object/Rule/RuleTest.php` - Exhaustive tests
- `api/tests/Framework/UnitTestCase.php` - Base test class
- `api/tests/Framework/Assertions/AggregateRootAssertions.php` - Event assertions