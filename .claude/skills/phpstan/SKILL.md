---
name: phpstan
description: Guide for PHPStan best practices and fixing errors properly. Use when encountering PHPStan errors or understanding project-specific rules.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# PHPStan Best Practices Guide

This Skill provides patterns for working with PHPStan in this project.

## When to Use

- Fixing PHPStan errors
- Understanding custom rules
- Adding proper type annotations
- Avoiding common mistakes

## Critical Rules (NEVER Do)

### 1. NEVER Modify `phpstan.dist.neon`

The configuration is locked. Do not:
- Add ignoreErrors
- Lower the level
- Exclude paths to hide errors

### 2. NEVER Use @phpstan-ignore

Avoid these annotations except in extremely rare, documented cases:
```php
// BAD - Never do this
/** @phpstan-ignore-next-line */
$result = $this->something();

// @phpstan-ignore argument.type
$this->method($value);
```

### 3. NEVER Lower Analysis Level

The project uses `level: max`. Do not request lowering it.

## Fixing Errors Properly

### Type Errors

```php
// ERROR: Parameter $value expects string, mixed given.

// FIX: Add type hint or assertion
Assert::string($value);
$this->process($value);
```

### Nullability Errors

```php
// ERROR: Cannot call method on possibly null value.

// FIX 1: Add null check
if (null !== $entity) {
    $entity->doSomething();
}

// FIX 2: Use null coalescing
$value = $entity?->getValue() ?? 'default';
```

### Generic/Collection Errors

```php
// ERROR: Method returns array, expected list<Item>

// FIX: Add proper PHPDoc
/**
 * @return list<Item>
 */
public function getItems(): array
{
    return $this->items;
}
```

### Method Return Type

```php
// ERROR: Method should return string but returns string|null

// FIX 1: Update return type
public function getValue(): ?string

// FIX 2: Ensure non-null return
public function getValue(): string
{
    return $this->value ?? throw new \RuntimeException('No value');
}
```

## PHPDoc Best Practices

### Array Types

```php
/**
 * @param array<string, mixed> $data    // Associative array
 * @param list<Item> $items              // Sequential array
 * @param array<int, string> $mapping    // Int-keyed array
 */
```

### Collection Types

```php
/**
 * @var Collection<int, Example>
 */
public private(set) Collection $examples;
```

### Template Types

```php
/**
 * @template T of object
 * @param class-string<T> $class
 * @return T
 */
public function get(string $class): object
```

### Throws Annotations

```php
/**
 * @throws RuleNotFoundException
 */
public function getRuleById(DirectiveId $id): Rule
```

## Project Custom Rules

### TestNameRule

Test methods must start with `testItShould`:

```php
// Good
public function testItShouldCreateRule(): void
public function testItShouldReturn404WhenNotFound(): void

// Bad
public function testCreateRule(): void
public function test_it_creates_rule(): void
```

### UseCaseRule

Handler classes must follow CQRS conventions:

1. Handler must implement `QueryHandler` or `CommandHandler`
2. Input class must be in same namespace as Handler
3. `__invoke` must have exactly one parameter named `input`
4. Input must implement `Command` or `Query` interface
5. QueryHandler must return an Output (not void)

### RepositoryMethodRule

Repository interface methods must follow naming:

| Prefix    | Return Type  | Requirement                       |
|-----------|--------------|-----------------------------------|
| `get`     | Non-nullable | `@throws EntityNotFoundException` |
| `find`    | Nullable     | No exception                      |
| `count`   | `int`        | -                                 |
| `search`  | `array`      | -                                 |

## Running PHPStan

```bash
castor phpstan       # Standard run
```

## When Stuck

1. **Read the error message carefully** - PHPStan messages are descriptive
2. **Check the exact line** - The issue is often nearby
3. **Add type hints** - Most errors are type-related
4. **Use Assert** - For runtime type enforcement
5. **Add PHPDoc** - For complex types PHPStan can't infer

## Reference Files

- `api/phpstan.dist.neon` - Configuration (read-only)
- `api/tools/phpstan/src/Rules/TestNameRule.php`
- `api/tools/phpstan/src/Rules/UseCaseRule.php`
- `api/tools/phpstan/src/Rules/RepositoryMethodRule.php`