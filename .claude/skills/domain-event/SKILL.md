---
name: domain-event
description: Guide for implementing Domain Events following DDD principles. Use when adding events to aggregates or creating event listeners.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# Domain Event Implementation Guide

This Skill provides patterns for implementing Domain Events in a DDD context.

## When to Use

- Recording significant state changes in aggregates
- Enabling cross-context communication
- Triggering side effects (notifications, sync, audit)
- Creating event listeners

## Core Concepts

### What is a Domain Event?

A Domain Event captures **something that happened** in business terms:
- `DirectiveDrafted` - A directive was created as a draft
- `DirectivePublished` - A directive was published
- `DirectiveArchived` - A directive was archived

### Events vs Commands

| Events | Commands |
|--------|----------|
| Past tense (`DirectivePublished`) | Imperative (`PublishDirective`) |
| Describe what happened | Request an action |
| Immutable | Can be validated/rejected |
| Multiple listeners | Single handler |

## Directory Structure

```
src/{BoundedContext}/Domain/Object/{Aggregate}/Event/
├── {Aggregate}Created.php
├── {Aggregate}Updated.php
└── {Aggregate}Archived.php

src/{BoundedContext}/UserInterface/Listener/
└── {Aggregate}PublishedListener.php
```

## Event Implementation

```php
namespace Dairectiv\{Context}\Domain\Object\{Aggregate}\Event;

use Dairectiv\SharedKernel\Domain\Object\Event\DomainEvent;

final readonly class {Aggregate}Created implements DomainEvent
{
    public function __construct(
        public {Aggregate}Id $aggregateId,
        // Only include immutable identity/data needed by listeners
    ) {}
}
```

**Key rules:**
- Always `final readonly`
- Implement `DomainEvent` interface
- Include only aggregate identity and essential data
- Use past tense naming

## Recording Events in Aggregates

Aggregates extend `AggregateRoot` and use `recordEvent()`:

```php
abstract class AggregateRoot
{
    protected function recordEvent(DomainEvent $event): void
    {
        DomainEventQueue::recordEvent($event);
    }
}
```

**Usage in aggregate:**
```php
public static function draft(DirectiveId $id, string $name): static
{
    $entity = new self();
    $entity->id = $id;
    $entity->name = $name;

    $entity->recordEvent(new DirectiveDrafted($entity->id));

    return $entity;
}

public function publish(): void
{
    Assert::eq($this->state, DirectiveState::Draft, 'Only drafts can be published.');

    $this->state = DirectiveState::Published;
    $this->recordEvent(new DirectivePublished($this->id));
}
```

## Event Listeners

Listeners live in `UserInterface/` layer (like controllers, they are entry points to the application layer).

```php
namespace Dairectiv\{Context}\UserInterface\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(DirectivePublished::class)]
final readonly class DirectivePublishedListener
{
    public function __construct(
        private CommandBus $commandBus,
    ) {}

    public function __invoke(DirectivePublished $event): void
    {
        // NO LOGIC HERE - only dispatch commands
        $this->commandBus->execute(new NotifySubscribersCommand($event->directiveId));
        $this->commandBus->execute(new UpdateSearchIndexCommand($event->directiveId));
    }
}
```

### Naming Convention

Listeners must be suffixed with `Listener`:
- `DirectivePublishedListener`
- `RuleArchivedListener`
- `WorkflowCreatedListener`

### Critical Rules for Listeners

1. **No business logic** - Listeners are entry points, not orchestrators
2. **Only dispatch commands** - Via CommandBus
3. **One listener per event** - Or use multiple `#[AsEventListener]` attributes
4. **Fail gracefully** - Don't break the main flow
5. **Suffix with `Listener`** - Always use `{Event}Listener` naming

## Event Dispatch Flow

```
Aggregate::publish()
    └─→ recordEvent(DirectivePublished)
            └─→ DomainEventQueue::recordEvent()
                    └─→ [After transaction commits via Middleware]
                            └─→ Symfony Messenger dispatches event
                                    └─→ DirectivePublishedListener::__invoke()
                                            └─→ CommandBus::execute(...)
```

## Testing Events

### In Unit Tests (Aggregate)

```php
public function testItShouldPublishDirective(): void
{
    $directive = Directive::draft($id, 'Name', 'Desc');
    $this->resetDomainEvents();

    $directive->publish();

    $this->assertDomainEventRecorded(DirectivePublished::class);
}
```

### In Integration Tests (Listener)

```php
public function testItShouldDispatchNotificationOnPublish(): void
{
    $directive = $this->createDirective();

    $this->execute(new PublishDirectiveCommand($directive->id));

    self::assertDomainEventHasBeenDispatched(DirectivePublished::class);
}
```

## Checklist

When creating a Domain Event:
- [ ] Past tense naming (`{Aggregate}{Action}ed`)
- [ ] Implements `DomainEvent` interface
- [ ] `final readonly` class
- [ ] Contains only aggregate identity + essential data
- [ ] Recorded in aggregate via `recordEvent()`

When creating a Listener:
- [ ] Lives in `UserInterface/Listener/`
- [ ] Named `{Event}Listener` (always suffixed with `Listener`)
- [ ] Uses `#[AsEventListener({Event}::class)]`
- [ ] Injects `CommandBus` in constructor
- [ ] Contains NO business logic
- [ ] Only dispatches commands

## Reference Files

- `api/src/SharedKernel/Domain/Object/Event/DomainEvent.php`
- `api/src/SharedKernel/Domain/Object/Event/DomainEventQueue.php`
- `api/src/SharedKernel/Domain/Object/AggregateRoot.php`
- `api/src/Authoring/Domain/Object/Directive/Event/DirectiveDrafted.php`