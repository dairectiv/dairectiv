---
name: api-testing
description: Guide for writing integration tests for API endpoints. Use when creating endpoint tests, testing validation, or asserting JSON responses.
allowed-tools: Read, Write, Edit, Glob, Grep
---

# API Testing Guide

This Skill provides patterns for writing integration tests for HTTP API endpoints.

## When to Use

- Testing a new API endpoint
- Writing validation tests with DataProviders
- Asserting JSON responses
- Testing domain event dispatching from controllers

## Test Class Structure

```php
namespace Dairectiv\Tests\Integration\{Context}\UserInterface\Http\Api\{Aggregate};

use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('{bounded-context}')]
#[Group('api')]
final class {Action}{Aggregate}Test extends IntegrationTestCase
{
    // Tests...
}
```

## Required Test Groups

All API tests must have these three groups:

```php
#[Group('integration')]  // Integration test category
#[Group('authoring')]    // Bounded context (authoring, shared-kernel, etc.)
#[Group('api')]          // API endpoint test
```

## HTTP Helper Methods

`IntegrationTestCase` provides these helpers:

```php
// POST request with JSON body
$this->postJson('/api/authoring/rules', ['name' => 'My Rule', ...]);

// GET request
$this->getJson('/api/authoring/rules/my-rule');

// PUT request with JSON body (full replacement)
$this->putJson('/api/authoring/rules/my-rule', ['name' => 'Updated', ...]);

// PATCH request with JSON body (partial update)
$this->patchJson('/api/authoring/rules/my-rule', ['name' => 'Updated']);

// DELETE request
$this->deleteJson('/api/authoring/rules/my-rule');
```

## Response Assertions

### Status Code

```php
self::assertResponseIsSuccessful();          // 2xx
self::assertResponseStatusCodeSame(Response::HTTP_CREATED);     // 201
self::assertResponseStatusCodeSame(Response::HTTP_OK);          // 200
self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);  // 204
self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);   // 404
self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);    // 409
self::assertResponseIsUnprocessable();       // 422
```

### JSON Content

```php
// Exact match with PHPMatcher patterns
self::assertResponseReturnsJson([
    'id'          => 'my-rule',
    'name'        => 'My Rule',
    'description' => '@string@',           // Any string
    'examples'    => '@array@',            // Any array
    'content'     => '@null@',             // Null value
    'createdAt'   => '@string@.isDateTime()', // DateTime string
    'state'       => '@string@',
]);
```

### Validation Errors (422)

```php
self::assertUnprocessableResponse([
    ['propertyPath' => 'name', 'title' => 'This value should not be blank.'],
    ['propertyPath' => 'description', 'title' => 'This value is too long. It should have 500 characters or less.'],
]);
```

## Domain Event Assertions

**CRITICAL**: All domain events dispatched during a test MUST be asserted.

```php
// Assert event was dispatched exactly once
self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);

// Assert event was dispatched multiple times
self::assertDomainEventHasBeenDispatched(ExampleAdded::class, times: 2);
```

If you don't assert all dispatched events, the test will fail in `assertPostConditions()`.

## DataProvider Pattern for Validation

```php
/**
 * @return iterable<string, array{payload: array<string, mixed>, expectedViolations: list<array{propertyPath: string, title: string}>}>
 */
public static function provideInvalidPayloads(): iterable
{
    yield 'empty payload' => [
        'payload'            => [],
        'expectedViolations' => [
            ['propertyPath' => 'name', 'title' => 'This value should be of type string.'],
            ['propertyPath' => 'description', 'title' => 'This value should be of type string.'],
        ],
    ];

    yield 'blank name' => [
        'payload'            => ['name' => '', 'description' => 'Valid'],
        'expectedViolations' => [
            ['propertyPath' => 'name', 'title' => 'This value should not be blank.'],
        ],
    ];

    yield 'name too long' => [
        'payload'            => ['name' => str_repeat('a', 256), 'description' => 'Valid'],
        'expectedViolations' => [
            ['propertyPath' => 'name', 'title' => 'This value is too long. It should have 255 characters or less.'],
        ],
    ];
}

/**
 * @param array<string, mixed> $payload
 * @param array<array{propertyPath: string, title: string}> $expectedViolations
 */
#[DataProvider('provideInvalidPayloads')]
public function testItShouldBeUnprocessable(array $payload, array $expectedViolations): void
{
    $this->postJson('/api/authoring/rules', $payload);

    self::assertUnprocessableResponse($expectedViolations);
}
```

## Test Naming Convention

Test methods MUST start with `testItShould`:

```php
// Good
public function testItShouldDraftRule(): void
public function testItShouldReturn404WhenNotFound(): void
public function testItShouldBeUnprocessable(array $payload, ...): void
public function testItShouldBeInConflictDueToRuleAlreadyExists(): void

// Bad
public function testDraftRule(): void
public function test_it_should_draft_rule(): void
```

## Complete Test Example

```php
#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class DraftRuleTest extends IntegrationTestCase
{
    public function testItShouldDraftRule(): void
    {
        // Arrange - nothing to set up

        // Act
        $this->postJson('/api/authoring/rules', [
            'name'        => 'My Rule',
            'description' => 'Description',
        ]);

        // Assert - Response
        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
        self::assertResponseReturnsJson([
            'id'          => '@string@',
            'name'        => 'My Rule',
            'description' => 'Description',
            'state'       => 'draft',
            'createdAt'   => '@string@.isDateTime()',
            'updatedAt'   => '@string@.isDateTime()',
        ]);

        // Assert - Domain Event
        self::assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
    }

    public function testItShouldReturn404WhenRuleNotFound(): void
    {
        $this->getJson('/api/authoring/rules/non-existent');

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testItShouldReturn409WhenRuleAlreadyExists(): void
    {
        // Arrange
        $rule = self::draftRuleEntity('my-rule');
        $this->persistEntity($rule);

        // Act
        $this->postJson('/api/authoring/rules', [
            'name'        => 'My Rule',
            'description' => 'Description',
        ]);

        // Assert
        self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
    }
}
```

## Entity Helpers

Use `persistEntity()` to set up test data:

```php
// Create and persist entity
$rule = self::draftRuleEntity('my-rule');
$this->persistEntity($rule);

// Find persisted entity
$persisted = $this->findEntity(Rule::class, ['id' => $id], strict: true);
```

## Override Helper for Partial Payloads

```php
private static function validPayload(): array
{
    return [
        'name'        => 'My Rule',
        'description' => 'Description',
    ];
}

public function testItShouldAcceptMinimalPayload(): void
{
    $this->postJson('/api/authoring/rules', self::override(
        self::validPayload(),
        ['name' => 'Different Name'],
    ));

    self::assertResponseIsSuccessful();
}
```

## Testing Priorities

1. **Use Case Tests** (most important) - Test business logic via CommandBus/QueryBus
2. **API Endpoint Tests** - Test HTTP layer, validation, response format
3. **Repository Tests** - Test data persistence

API tests should focus on:
- HTTP status codes
- JSON response structure
- Validation errors
- Domain event dispatching
- Edge cases (not found, conflict)

## Checklist

When writing API tests:
- [ ] Three test groups: `integration`, `{bounded-context}`, `api`
- [ ] Test method starts with `testItShould`
- [ ] Happy path tests response status, JSON, and domain events
- [ ] Validation tests use DataProvider pattern
- [ ] 404 test for entity not found
- [ ] 409 test for conflict scenarios (if applicable)
- [ ] All dispatched domain events are asserted

## Reference Files

- `api/tests/Framework/IntegrationTestCase.php` - Base test class with all helpers
- `api/tests/Integration/Authoring/UserInterface/Http/Api/Rule/DraftRuleTest.php` - POST example
- `api/tests/Integration/Authoring/UserInterface/Http/Api/Rule/GetRuleTest.php` - GET example
- `api/tests/Integration/Authoring/UserInterface/Http/Api/Rule/UpdateRuleTest.php` - PUT example