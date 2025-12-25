---
name: aggregate-root
description: Guide for implementing DDD Aggregate Roots with value objects, domain events, and tests. Use when creating new aggregates or extending existing ones.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# Aggregate Root Implementation Guide

This Skill provides patterns for implementing DDD Aggregate Roots in the dairectiv codebase.

## When to Use

Use this Skill when:
- Creating a new Aggregate Root
- Adding value objects to a domain
- Implementing domain events
- Writing tests for aggregates

## Directory Structure

```
src/{BoundedContext}/Domain/
├── {Aggregate}/
│   ├── {Aggregate}.php           # Aggregate root class
│   ├── {Aggregate}Id.php         # Identity value object
│   ├── {Aggregate}Change.php     # Change value object (for mutations)
│   └── Event/
│       ├── {Aggregate}Created.php
│       └── {Aggregate}Updated.php
└── Exception/
    └── {Aggregate}ConflictException.php
```

## Class Signatures

### Aggregate Root (Abstract)

```php
<?php

declare(strict_types=1);

namespace Dairectiv\{Context}\Domain\{Aggregate};

use Cake\Chronos\Chronos;
use Dairectiv\SharedKernel\Domain\AggregateRoot;

/**
 * @template T of Change
 */
abstract class {Aggregate} extends AggregateRoot
{
    private(set) {Aggregate}Id $id;
    private(set) {Aggregate}State $state;
    private(set) {Aggregate}Version $version;
    private(set) Chronos $createdAt;
    private(set) Chronos $updatedAt;

    /**
     * @param T $change
     */
    abstract protected function doApplyChanges(Change $change): void;

    public static function create({Aggregate}Id $id, /* other params */): static
    {
        $aggregate = new static();
        $aggregate->id = $id;
        $aggregate->version = {Aggregate}Version::initial();
        $aggregate->state = {Aggregate}State::Draft;
        $aggregate->createdAt = Chronos::now();
        $aggregate->updatedAt = Chronos::now();

        $aggregate->recordEvent(new {Aggregate}Created($aggregate->id));

        return $aggregate;
    }

    /**
     * @param T $change
     */
    final public function applyChanges(Change $change, {Aggregate}Version $expectedVersion): void
    {
        if (!$this->version->equals($expectedVersion)) {
            throw new {Aggregate}ConflictException($expectedVersion, $this);
        }

        $this->doApplyChanges($change);
        $this->updatedAt = Chronos::now();
        $this->version = $this->version->increment();

        $this->recordEvent(new {Aggregate}Updated($this->id, $this->version));
    }
}
```

### Concrete Aggregate

```php
<?php

declare(strict_types=1);

namespace Dairectiv\{Context}\Domain\{Aggregate};

/**
 * @extends {Parent}<{Aggregate}Change>
 */
final class {Aggregate} extends {Parent}
{
    // Type-specific properties
    private(set) {Aggregate}Content $content;

    protected function doApplyChanges(Change $change): void
    {
        if ($change->content !== null) {
            $this->content = $change->content;
        }
        // Apply other fields...
    }
}
```

## Value Objects

### Identity Value Object

```php
<?php

declare(strict_types=1);

namespace Dairectiv\{Context}\Domain\{Aggregate};

use Dairectiv\SharedKernel\Domain\Assert;

final readonly class {Aggregate}Id implements \Stringable
{
    private function __construct(public string $id)
    {
    }

    public static function fromString(string $id): self
    {
        Assert::kebabCase($id, \sprintf('{Aggregate} id "%s" is not in kebab-case.', $id));

        return new self($id);
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
```

### Version Value Object

```php
<?php

declare(strict_types=1);

namespace Dairectiv\{Context}\Domain\{Aggregate};

final readonly class {Aggregate}Version implements \Stringable
{
    private function __construct(public int $version)
    {
    }

    public static function initial(): self
    {
        return new self(1);
    }

    public function increment(): self
    {
        return new self($this->version + 1);
    }

    public function equals(self $version): bool
    {
        return $this->version === $version->version;
    }

    public function isOlderThan(self $version): bool
    {
        return $this->version < $version->version;
    }

    public function isNewerThan(self $version): bool
    {
        return $this->version > $version->version;
    }

    public function __toString(): string
    {
        return \sprintf('v%d', $this->version);
    }
}
```

### Change Value Object

```php
<?php

declare(strict_types=1);

namespace Dairectiv\{Context}\Domain\{Aggregate};

use Dairectiv\{Context}\Domain\ChangeSet\Change;

final readonly class {Aggregate}Change extends Change
{
    public function __construct(
        public ?{Aggregate}Name $name = null,
        public ?{Aggregate}Content $content = null,
        // null = no change, value = apply change
    ) {
    }
}
```

## Domain Events

### Event Structure

```php
<?php

declare(strict_types=1);

namespace Dairectiv\{Context}\Domain\{Aggregate}\Event;

use Dairectiv\SharedKernel\Domain\Event\DomainEvent;

final readonly class {Aggregate}Created implements DomainEvent
{
    public function __construct(
        public {Aggregate}Id $aggregateId,
    ) {
    }
}
```

## Exceptions

### Conflict Exception

```php
<?php

declare(strict_types=1);

namespace Dairectiv\{Context}\Domain\{Aggregate}\Exception;

final class {Aggregate}ConflictException extends \RuntimeException
{
    public function __construct(
        public readonly {Aggregate}Version $expectedVersion,
        public readonly {Aggregate} $aggregate,
    ) {
        parent::__construct(\sprintf(
            'Conflict: expected version %s but aggregate has version %s',
            $expectedVersion,
            $aggregate->version,
        ));
    }
}
```

## Testing

### Test Class Structure

```php
<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\{Context}\Domain\{Aggregate};

use Dairectiv\Tests\Framework\AggregateRootAssertions;
use PHPUnit\Framework\TestCase;

final class {Aggregate}Test extends TestCase
{
    use AggregateRootAssertions;

    public function testItShouldCreate{Aggregate}WithInitialState(): void
    {
        $aggregate = {Aggregate}::create(
            {Aggregate}Id::fromString('my-aggregate'),
        );

        self::assertSame(1, $aggregate->version->version);

        // REQUIRED: Assert all domain events
        $this->assertDomainEventRecorded({Aggregate}Created::class);
    }

    public function testItShouldApplyChangesWithCorrectVersion(): void
    {
        $aggregate = {Aggregate}::create(
            {Aggregate}Id::fromString('my-aggregate'),
        );

        $this->resetDomainEvents(); // Clear setup events

        $aggregate->applyChanges(
            new {Aggregate}Change(/* changes */),
            {Aggregate}Version::initial(),
        );

        self::assertSame(2, $aggregate->version->version);

        $event = $this->assertDomainEventRecorded({Aggregate}Updated::class);
        self::assertSame(2, $event->version->version);
    }

    public function testItShouldThrowConflictOnVersionMismatch(): void
    {
        $aggregate = {Aggregate}::create(
            {Aggregate}Id::fromString('my-aggregate'),
        );

        $this->assertDomainEventRecorded({Aggregate}Created::class);

        $this->expectException({Aggregate}ConflictException::class);

        $aggregate->applyChanges(
            new {Aggregate}Change(),
            {Aggregate}Version::initial()->increment(), // Wrong version
        );
    }
}
```

### AggregateRootAssertions Trait

The trait provides:
- `assertDomainEventRecorded(string $class): DomainEvent` - Assert and return event
- `assertNoDomainEvents(): void` - Assert no events recorded
- `resetDomainEvents(): void` - Clear events mid-test
- `assertPostConditions(): void` - Fails if unasserted events exist

**IMPORTANT**: Every test MUST assert all domain events or the test will fail in `assertPostConditions()`.

## Checklist

When implementing a new Aggregate Root:

- [ ] Create aggregate class extending `AggregateRoot`
- [ ] Create identity value object (`{Aggregate}Id`)
- [ ] Create version value object if needed (`{Aggregate}Version`)
- [ ] Create state enum if needed (`{Aggregate}State`)
- [ ] Create change value object (`{Aggregate}Change`)
- [ ] Create domain events (`{Aggregate}Created`, `{Aggregate}Updated`, etc.)
- [ ] Create conflict exception (`{Aggregate}ConflictException`)
- [ ] Write tests using `AggregateRootAssertions` trait
- [ ] Ensure 100% test coverage
- [ ] All events asserted in tests