---
name: doctrine-mapping
description: Guide for Doctrine entity/aggregate mapping conventions. Use when mapping entities, configuring relationships, or adding custom types.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# Doctrine Mapping Guide

This Skill provides conventions for mapping entities with Doctrine ORM.

## When to Use

- Mapping a new aggregate or entity
- Adding relationships (OneToMany, ManyToOne)
- Configuring custom types
- Understanding naming conventions

## Configuration

Doctrine is configured in `api/config/packages/doctrine.yaml`:

```yaml
doctrine:
    dbal:
        types:
            # Custom types registered here
            chronos: Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types\ChronosType
            authoring_directive_id: Dairectiv\Authoring\Infrastructure\Doctrine\DBAL\Types\DirectiveIdType

    orm:
        naming_strategy: doctrine.orm.naming_strategy.underscore_number_aware
        mappings:
            Authoring:
                type: attribute
                dir: '%kernel.project_dir%/src/Authoring/Domain'
                prefix: 'Dairectiv\Authoring\Domain'
```

**Key points:**
- Uses `underscore_number_aware` naming strategy (camelCase → snake_case)
- Mappings are per bounded context
- Entities live in Domain layer but use Doctrine attributes

## Entity Mapping

### Basic Entity

```php
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'authoring_directive')]
class Directive extends AggregateRoot
{
    #[ORM\Id]
    #[ORM\Column(type: 'authoring_directive_id')]
    public private(set) DirectiveId $id;

    #[ORM\Column(type: Types::STRING)]
    public private(set) string $name;

    #[ORM\Column(type: Types::TEXT)]
    public private(set) string $description;

    #[ORM\Column(type: 'chronos')]
    public private(set) Chronos $createdAt;
}
```

### Table Naming

- Use explicit `#[ORM\Table(name: '...')]` for aggregate roots
- Format: `{bounded_context}_{aggregate}` (e.g., `authoring_directive`)
- Child entities inherit table from single table inheritance or get own table

### Column Naming

The `underscore_number_aware` strategy handles column naming automatically:
- `createdAt` → `created_at`
- `directiveId` → `directive_id`

For explicit naming, use the `name` parameter:
```php
#[ORM\Column(name: 'workflow_content', type: Types::TEXT, nullable: true)]
public private(set) ?string $content = null;
```

## Property Visibility

Use PHP 8.4 asymmetric visibility:

```php
// Public read, private write
public private(set) DirectiveId $id;

// Nullable property
public private(set) ?string $content = null;
```

## Custom Types

Reference custom types by their registered name in `doctrine.yaml`:

```php
// Custom type for value objects
#[ORM\Column(type: 'authoring_directive_id')]
public private(set) DirectiveId $id;

// Custom type for dates
#[ORM\Column(type: 'chronos')]
public private(set) Chronos $createdAt;

// Built-in types from Doctrine\DBAL\Types\Types
#[ORM\Column(type: Types::STRING)]
#[ORM\Column(type: Types::TEXT)]
#[ORM\Column(type: Types::INTEGER)]
#[ORM\Column(type: Types::BOOLEAN)]
```

## Relationships

### OneToMany (Parent → Children)

```php
/**
 * @var Collection<int, Example>
 */
#[ORM\OneToMany(
    targetEntity: Example::class,
    mappedBy: 'rule',
    cascade: ['persist'],
    orphanRemoval: true,
    fetch: 'EAGER'
)]
public private(set) Collection $examples;
```

**Options:**
- `cascade: ['persist']` - Auto-persist children when parent is saved
- `orphanRemoval: true` - Delete children when removed from collection
- `fetch: 'EAGER'` - Load children immediately (use for small collections)

### ManyToOne (Child → Parent)

```php
#[ORM\ManyToOne(targetEntity: Rule::class, inversedBy: 'examples')]
#[ORM\JoinColumn(nullable: false)]
public private(set) Rule $rule;
```

### Ordered Collections

```php
#[ORM\OneToMany(targetEntity: Step::class, mappedBy: 'workflow', ...)]
#[ORM\OrderBy(['order' => 'ASC'])]
public private(set) Collection $steps;
```

## Inheritance

### Single Table Inheritance

```php
#[ORM\Entity]
#[ORM\Table(name: 'authoring_directive')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['rule' => Rule::class, 'workflow' => Workflow::class])]
abstract class Directive extends AggregateRoot
{
    // Common fields
}

#[ORM\Entity]
class Rule extends Directive
{
    // Rule-specific fields
    #[ORM\Column(name: 'rule_content', type: Types::TEXT, nullable: true)]
    public private(set) ?string $content = null;
}
```

## Enum Mapping

```php
#[ORM\Column(type: 'string', enumType: DirectiveState::class)]
public private(set) DirectiveState $state;
```

## Collection Initialization

Always initialize collections in constructor:

```php
public function __construct()
{
    $this->examples = new ArrayCollection();
    $this->steps = new ArrayCollection();
}
```

## Checklist

When mapping an entity:
- [ ] Add `#[ORM\Entity]` attribute
- [ ] Add `#[ORM\Table(name: '...')]` for aggregate roots
- [ ] Use custom types for value objects (registered in doctrine.yaml)
- [ ] Use `Types::*` constants for built-in types
- [ ] Add PHPDoc `@var Collection<int, Entity>` for collections
- [ ] Initialize collections in constructor
- [ ] Use asymmetric visibility (`public private(set)`)

## Reference Files

- `api/config/packages/doctrine.yaml` - Doctrine configuration
- `api/src/Authoring/Domain/Object/Directive/Directive.php` - Inheritance example
- `api/src/Authoring/Domain/Object/Workflow/Workflow.php` - Relationships example