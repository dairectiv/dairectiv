---
name: value-object
description: Guide for implementing immutable Value Objects with base classes (StringValue, UuidValue) and Doctrine custom types. Use when creating identity objects or domain-specific values.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# Value Object Implementation Guide

This Skill provides patterns for implementing immutable Value Objects.

## When to Use

- Creating identity value objects (IDs)
- Wrapping primitive values with domain meaning
- Creating Doctrine custom types for persistence

## Base Classes

### StringValue

For string-based value objects with validation:

```php
final readonly class DirectiveId extends StringValue
{
    public static function validate(string $value): void
    {
        Assert::kebabCase($value, \sprintf('ID "%s" is not kebab-case.', $value));
    }
}
```

**Usage:**
```php
$id = DirectiveId::fromString('my-rule');
echo $id; // "my-rule"
$id->equals($otherId); // bool
```

### UuidValue

For UUID-based identities (extends Symfony Uuid):

```php
final class ExampleId extends UuidValue
{
}
```

**Usage:**
```php
$id = ExampleId::generate(); // Creates UUID v7
$id = ExampleId::setNext('550e8400-e29b-41d4-a716-446655440000'); // For tests
```

### ObjectValue

For complex value objects with JSON serialization:

```php
final readonly class RuleContent extends ObjectValue
{
    public function __construct(
        public string $body,
        public array $tags,
    ) {}

    public function toArray(): array
    {
        return ['body' => $this->body, 'tags' => $this->tags];
    }

    public static function fromArray(array $data): static
    {
        return new self($data['body'], $data['tags']);
    }
}
```

## Directory Structure

```
src/{BoundedContext}/Domain/Object/{Aggregate}/
├── {Entity}Id.php           # Identity value object

src/{BoundedContext}/Infrastructure/Doctrine/DBAL/Types/
├── {Entity}IdType.php       # Doctrine type

tests/Integration/{BoundedContext}/Infrastructure/Doctrine/DBAL/
├── {Entity}IdTypeTest.php   # Type test
```

## Doctrine Custom Types

### For StringValue

```php
namespace Dairectiv\{Context}\Infrastructure\Doctrine\DBAL\Types;

final class {Entity}IdType extends StringValueType
{
    protected function getStringValueClass(): string
    {
        return {Entity}Id::class;
    }
}
```

### For UuidValue

```php
final class ExampleIdType extends UuidValueType
{
    protected function getUuidValueClass(): string
    {
        return ExampleId::class;
    }
}
```

### For ObjectValue

```php
final class RuleContentType extends ObjectValueType
{
    protected function getObjectValueClass(): string
    {
        return RuleContent::class;
    }
}
```

### Registration in doctrine.yaml

```yaml
doctrine:
    dbal:
        types:
            authoring_directive_id: Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types\DirectiveIdType
            authoring_example_id: Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types\RuleExampleIdType
```

## Mapping in Entity

Use the registered type name in your entity mapping:

```php
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Rule extends Directive
{
    // Identity with StringValue type
    #[ORM\Id]
    #[ORM\Column(type: 'authoring_directive_id')]
    public private(set) DirectiveId $id;

    // UuidValue type for child entities
    #[ORM\Id]
    #[ORM\Column(type: 'authoring_example_id')]
    public private(set) ExampleId $id;

    // ObjectValue type for complex JSON data
    #[ORM\Column(type: 'authoring_rule_content', nullable: true)]
    public private(set) ?RuleContent $content = null;
}
```

**Key points:**
- Use the type name registered in `doctrine.yaml`, not the class name
- For nullable value objects, add `nullable: true`
- The type handles conversion to/from database automatically

## Key Rules

1. **Always readonly** - Value objects are immutable
2. **Private constructor** - Use factory methods (`fromString()`, `generate()`)
3. **Implement validation** - Override `validate()` for StringValue
4. **Implement Stringable** - For easy conversion and debugging
5. **Implement equals()** - For comparison

## Testing Custom Types

```php
#[Group('integration')]
#[Group('{bounded-context}')]
final class DirectiveIdTypeTest extends IntegrationTestCase
{
    public function testItShouldConvertToDatabaseValue(): void
    {
        $id = DirectiveId::fromString('my-rule');

        $this->assertConvertToDatabaseValue('my-rule', $id, 'authoring_directive_id');
    }

    public function testItShouldConvertToPHPValue(): void
    {
        $expected = DirectiveId::fromString('my-rule');

        $this->assertConvertToPhpValue($expected, 'my-rule', 'authoring_directive_id');
    }

    public function testItShouldHandleNull(): void
    {
        $this->assertConvertToDatabaseValue(null, null, 'authoring_directive_id');
        $this->assertConvertToPhpValue(null, null, 'authoring_directive_id');
    }
}
```

## Reference Files

- `api/src/SharedKernel/Domain/Object/ValueObject/StringValue.php`
- `api/src/SharedKernel/Domain/Object/ValueObject/UuidValue.php`
- `api/src/SharedKernel/Domain/Object/ValueObject/ObjectValue.php`
- `api/src/Authoring/Domain/Object/Directive/DirectiveId.php`
- `api/src/SharedKernel/Infrastructure/Doctrine/DBAL/Types/StringValueType.php`
- `api/config/packages/doctrine.yaml`