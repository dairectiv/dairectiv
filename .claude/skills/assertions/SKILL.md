---
name: assertions
description: Guide for using Assert class (webmozart/assert) for data validation and type enforcement. Use when adding validation to value objects or domain methods.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# Assertions Guide

This Skill provides patterns for using the Assert class for validation.

## When to Use

- Validating data in Value Object factories
- Enforcing business invariants in aggregates
- Type enforcement when PHP's type system is insufficient
- Guard clauses in domain methods

## Assert Class

The project extends `Webmozart\Assert\Assert` with custom behavior:

```php
namespace Dairectiv\SharedKernel\Domain\Object;

final class Assert extends WebmozartAssert
{
    // Throws InvalidArgumentException instead of \InvalidArgumentException
    protected static function reportInvalidArgument(string $message): never
    {
        throw new InvalidArgumentException($message);
    }

    // Custom assertion for kebab-case validation
    public static function kebabCase(mixed $value, ?string $message = null): string
    {
        // ...
    }
}
```

**Key difference:** Throws `Dairectiv\...\InvalidArgumentException` (→ HTTP 422) instead of PHP's built-in exception.

## Common Assertions

### String Validation

```php
Assert::notEmpty($value, 'Name cannot be empty.');
Assert::maxLength($value, 255, 'Name exceeds 255 characters.');
Assert::minLength($value, 3, 'Name must be at least 3 characters.');
Assert::regex($value, '/^[a-z-]+$/', 'Invalid format.');
```

### Numeric Validation

```php
Assert::positiveInteger($value, 'Must be positive.');
Assert::range($value, 1, 100, 'Must be between 1 and 100.');
Assert::greaterThan($value, 0, 'Must be greater than zero.');
```

### Equality/State

```php
Assert::eq($state, DirectiveState::Draft, 'Only drafts can be published.');
Assert::notEq($state, DirectiveState::Archived, 'Already archived.');
Assert::true($condition, 'Condition must be true.');
Assert::false($condition, 'Condition must be false.');
```

### Type Enforcement

```php
Assert::string($value, 'Expected string.');
Assert::isInstanceOf($value, DirectiveId::class);
Assert::allIsInstanceOf($items, Item::class);
Assert::notNull($value, 'Value is required.');
```

### Custom: Kebab-Case

```php
Assert::kebabCase($id, \sprintf('ID "%s" must be kebab-case.', $id));
```

## Usage Patterns

### In Value Object Factory

```php
final readonly class DirectiveId extends StringValue
{
    public static function validate(string $value): void
    {
        Assert::maxLength($value, 200, \sprintf('ID "%s" exceeds 200 chars.', $value));
        Assert::kebabCase($value, \sprintf('ID "%s" is not kebab-case.', $value));
    }
}
```

### In Aggregate Methods

```php
public function publish(): void
{
    Assert::eq($this->state, DirectiveState::Draft, 'Only drafts can be published.');

    $this->state = DirectiveState::Published;
    $this->recordEvent(new DirectivePublished($this->id));
}

public function archive(): void
{
    Assert::notEq($this->state, DirectiveState::Archived, 'Already archived.');
    Assert::notEq($this->state, DirectiveState::Deleted, 'Cannot archive deleted.');

    // ...
}
```

### Guard Clause

```php
public function removeExample(Example $example): void
{
    Assert::true(
        $this->examples->contains($example),
        'Example does not belong to this rule.'
    );

    $this->examples->removeElement($example);
}
```

## Assert vs Symfony Validator

| Use Assert             | Use Symfony Validator  |
|------------------------|------------------------|
| Value Object factories | API request validation |
| Domain invariants      | DTO/Payload validation |
| Internal consistency   | User input validation  |
| Fast, in-memory checks | Form validation        |

**Rule of thumb:**
- **Assert** = Domain layer (protect invariants)
- **Symfony Validator** = Application/UserInterface layer (validate user input)

## Message Formatting

Always use `\sprintf()` with meaningful messages:

```php
// Good
Assert::maxLength($name, 255, \sprintf('Name "%s" exceeds 255 characters.', $name));

// Bad
Assert::maxLength($name, 255);  // Generic message
Assert::maxLength($name, 255, "Name too long");  // No context
```

## Testing Assertions

```php
public function testItShouldNotAcceptEmptyName(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Name cannot be empty.');

    DirectiveName::fromString('');
}

public function testItShouldNotAcceptNonKebabCaseId(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('ID "InvalidId" is not kebab-case.');

    DirectiveId::fromString('InvalidId');
}
```

## Checklist

When using Assert:
- [ ] Use project's `Assert` class (not `Webmozart\Assert\Assert`)
- [ ] Include context in messages (the value, the constraint)
- [ ] Use `\sprintf()` for message formatting
- [ ] Place assertions at the beginning of methods (guard clauses)
- [ ] Test both valid and invalid inputs

## Reference Files

- `api/src/SharedKernel/Domain/Object/Assert.php` - Custom Assert class
- `api/src/Authoring/Domain/Object/Directive/DirectiveId.php` - Usage in Value Object
- `api/src/Authoring/Domain/Object/Directive/Directive.php` - Usage in Aggregate