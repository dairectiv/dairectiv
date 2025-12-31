# API Endpoint Generator

This Skill helps generate REST API endpoints following the project conventions.

## When to Use

Use this Skill when the user:
- Asks to create a new API endpoint
- Wants to implement a REST action (GET, POST, PUT, DELETE)
- Says "create endpoint", "add API route", or similar

## Directory Structure

```
api/src/Authoring/UserInterface/Http/Api/
├── Controller/
│   └── {Resource}Controller.php
├── Payload/
│   └── {Resource}/
│       └── {Action}/
│           └── {Action}{Resource}Payload.php
└── Response/
    └── {Resource}/
        ├── {Resource}Response.php
        └── {SubResource}Response.php

api/tests/Integration/Authoring/UserInterface/Http/Api/
└── {Resource}/
    └── {Action}{Resource}Test.php

oas/openapi.yaml
```

## Step 1: Understand the Endpoint Requirements

Gather the following information:
- **Resource**: What entity/resource? (Rule, Workflow, etc.)
- **Action**: What operation? (Get, Draft, Update, Delete, etc.)
- **HTTP Method**: GET, POST, PUT, PATCH, DELETE
- **Use Case**: Which Application layer use case does it call?
- **Request Data**: What payload is expected? (for POST/PUT/PATCH)
- **Response Data**: What should be returned?
- **Error Cases**: What exceptions can occur?

## Step 2: Implement the Controller

### Controller Location
`api/src/Authoring/UserInterface/Http/Api/Controller/{Resource}Controller.php`

### Controller Template

```php
<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Controller;

use Dairectiv\Authoring\Application\{Resource}\{Action};
use Dairectiv\Authoring\Domain\Object\{Resource}\Exception\{Resource}NotFoundException;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\{Resource}\{Action}\{Action}{Resource}Payload;
use Dairectiv\Authoring\UserInterface\Http\Api\Response\{Resource}\{Resource}Response;
use Dairectiv\SharedKernel\Application\Command\CommandBus;
use Dairectiv\SharedKernel\Application\Query\QueryBus;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{resources}', name: '{resource}_')]
final class {Resource}Controller extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    // Add action methods here...
}
```

### GET Action (Query)

```php
#[Route('/{id}', name: 'get', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['GET'])]
public function get(string $id): JsonResponse
{
    try {
        $output = $this->queryBus->fetch(new Get\Input($id));

        Assert::isInstanceOf($output, Get\Output::class);

        return $this->json({Resource}Response::from{Resource}($output->{resource}));
    } catch ({Resource}NotFoundException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e);
    }
}
```

### POST Action (Command - Create)

```php
#[Route('', name: 'draft', methods: ['POST'])]
public function draft(#[MapRequestPayload] Draft{Resource}Payload $payload): JsonResponse
{
    try {
        $output = $this->commandBus->execute(new Draft\Input($payload->name, $payload->description));

        Assert::isInstanceOf($output, Draft\Output::class);

        return $this->json({Resource}Response::from{Resource}($output->{resource}), 201);
    } catch (DirectiveAlreadyExistsException $e) {
        throw new ConflictHttpException($e->getMessage(), $e);
    }
}
```

### PUT/PATCH Action (Command - Update)

```php
#[Route('/{id}', name: 'update', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['PUT'])]
public function update(string $id, #[MapRequestPayload] Update{Resource}Payload $payload): JsonResponse
{
    try {
        $output = $this->commandBus->execute(new Update\Input(
            $id,
            $payload->name,
            $payload->description,
        ));

        Assert::isInstanceOf($output, Update\Output::class);

        return $this->json({Resource}Response::from{Resource}($output->{resource}));
    } catch ({Resource}NotFoundException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e);
    }
}
```

### DELETE Action (Command)

```php
#[Route('/{id}', name: 'delete', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['DELETE'])]
public function delete(string $id): JsonResponse
{
    try {
        $this->commandBus->execute(new Delete\Input($id));

        return new JsonResponse(null, 204);
    } catch ({Resource}NotFoundException $e) {
        throw new NotFoundHttpException($e->getMessage(), $e);
    }
}
```

### HTTP Exception Mapping

| Domain Exception                               | HTTP Exception          | Status Code |
|------------------------------------------------|-------------------------|-------------|
| `{Resource}NotFoundException`                  | `NotFoundHttpException` | 404         |
| `DirectiveAlreadyExistsException`              | `ConflictHttpException` | 409         |
| `DirectiveArchivedException`                   | `ConflictHttpException` | 409         |
| Validation errors (via `#[MapRequestPayload]`) | Automatic               | 422         |

## Step 3: Implement the Payload (for POST/PUT/PATCH)

### Payload Location
`api/src/Authoring/UserInterface/Http/Api/Payload/{Resource}/{Action}/{Action}{Resource}Payload.php`

### Payload Template

```php
<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\{Resource}\{Action};

use Symfony\Component\Validator\Constraints;

final readonly class {Action}{Resource}Payload
{
    public function __construct(
        #[Constraints\NotBlank]
        #[Constraints\Length(max: 255)]
        public string $name,
        #[Constraints\NotBlank]
        #[Constraints\Length(max: 500)]
        public string $description,
    ) {
    }
}
```

### Common Validation Constraints

| Constraint                      | Usage           | Example                |
|---------------------------------|-----------------|------------------------|
| `#[Constraints\NotBlank]`       | Required field  | `public string $name`  |
| `#[Constraints\Length(max: N)]` | Max length      | `max: 255`             |
| `#[Constraints\Length(min: N)]` | Min length      | `min: 1`               |
| `#[Constraints\Type('string')]` | Type validation | `public string $field` |
| `#[Constraints\Uuid]`           | UUID format     | `public string $id`    |
| `#[Constraints\Email]`          | Email format    | `public string $email` |

### Optional Fields

For optional fields, use nullable types:

```php
public function __construct(
    #[Constraints\NotBlank]
    public string $name,
    #[Constraints\Length(max: 1000)]
    public ?string $content = null,
) {
}
```

## Step 4: Implement the Response

### Response Location
`api/src/Authoring/UserInterface/Http/Api/Response/{Resource}/{Resource}Response.php`

### Response Template

```php
<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\{Resource};

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\{Resource}\{Resource};

final readonly class {Resource}Response
{
    /**
     * @param list<{SubResource}Response> ${subResources}
     */
    private function __construct(
        public string $id,
        public Chronos $createdAt,
        public Chronos $updatedAt,
        public DirectiveState $state,
        public string $name,
        public string $description,
        public ?string $content,
        public array ${subResources},
    ) {
    }

    public static function from{Resource}({Resource} ${resource}): self
    {
        return new self(
            (string) ${resource}->id,
            ${resource}->createdAt,
            ${resource}->updatedAt,
            ${resource}->state,
            ${resource}->name,
            ${resource}->description,
            ${resource}->content,
            array_values(${resource}->{subResources}->map({SubResource}Response::from{SubResource}(...))->toArray()),
        );
    }
}
```

### Sub-Resource Response Template

```php
<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\{Resource};

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\{Resource}\{SubResource}\{SubResource};

final readonly class {SubResource}Response
{
    private function __construct(
        public string $id,
        public Chronos $createdAt,
        public Chronos $updatedAt,
        public ?string $field1,
        public ?string $field2,
    ) {
    }

    public static function from{SubResource}({SubResource} ${subResource}): self
    {
        return new self(
            ${subResource}->id->toString(),
            ${subResource}->createdAt,
            ${subResource}->updatedAt,
            ${subResource}->field1,
            ${subResource}->field2,
        );
    }
}
```

## Step 5: Implement Integration Tests

### Test Location
`api/tests/Integration/Authoring/UserInterface/Http/Api/{Resource}/{Action}{Resource}Test.php`

### Test Class Structure

```php
<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\UserInterface\Http\Api\{Resource};

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveDrafted;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('integration')]
#[Group('authoring')]
#[Group('api')]
final class {Action}{Resource}Test extends IntegrationTestCase
{
    // Test methods...
}
```

### GET Endpoint Tests

```php
public function testItShouldGet{Resource}(): void
{
    ${resource} = self::draft{Resource}Entity();
    $this->persistEntity(${resource});

    $this->get{Resource}();

    self::assertResponseIsSuccessful();

    IntegrationTestCase::assertResponseReturnsJson([
        'id'          => (string) ${resource}->id,
        'name'        => ${resource}->name,
        'description' => ${resource}->description,
        'state'       => 'draft',
        'updatedAt'   => Chronos::now()->toIso8601String(),
        'createdAt'   => Chronos::now()->toIso8601String(),
    ]);
}

public function testItShouldReturn404When{Resource}NotFound(): void
{
    $this->get{Resource}('non-existent-id');

    self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
}

private function get{Resource}(string $id = '{resource}-id'): void
{
    $this->getJson(\sprintf('/api/authoring/{resources}/%s', $id));
}
```

### POST Endpoint Tests

```php
public function testItShouldDraft{Resource}(): void
{
    $this->draft{Resource}();

    self::assertResponseIsSuccessful();
    self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

    IntegrationTestCase::assertResponseReturnsJson([
        'id'          => 'my-{resource}',
        'name'        => 'My {Resource}',
        'description' => 'Description',
        'state'       => 'draft',
        'updatedAt'   => Chronos::now()->toIso8601String(),
        'createdAt'   => Chronos::now()->toIso8601String(),
    ]);

    $this->assertDomainEventHasBeenDispatched(DirectiveDrafted::class);
}

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
        'payload'            => ['name' => '', 'description' => 'Description'],
        'expectedViolations' => [
            ['propertyPath' => 'name', 'title' => 'This value should not be blank.'],
        ],
    ];

    yield 'blank description' => [
        'payload'            => ['name' => 'My {Resource}', 'description' => ''],
        'expectedViolations' => [
            ['propertyPath' => 'description', 'title' => 'This value should not be blank.'],
        ],
    ];

    yield 'name too long' => [
        'payload'            => ['name' => str_repeat('a', 256), 'description' => 'Description'],
        'expectedViolations' => [
            ['propertyPath' => 'name', 'title' => 'This value is too long. It should have 255 characters or less.'],
        ],
    ];

    yield 'description too long' => [
        'payload'            => ['name' => 'My {Resource}', 'description' => str_repeat('a', 501)],
        'expectedViolations' => [
            ['propertyPath' => 'description', 'title' => 'This value is too long. It should have 500 characters or less.'],
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
    $this->postJson('/api/authoring/{resources}', $payload);

    self::assertUnprocessableResponse($expectedViolations);
}

public function testItShouldBeInConflictDue To{Resource}AlreadyExists(): void
{
    ${resource} = self::draft{Resource}Entity('my-{resource}');
    $this->persistEntity(${resource});

    $this->draft{Resource}();

    self::assertResponseStatusCodeSame(Response::HTTP_CONFLICT);
}

private function draft{Resource}(
    string $name = 'My {Resource}',
    string $description = 'Description',
): void {
    $this->postJson('/api/authoring/{resources}', [
        'name'        => $name,
        'description' => $description,
    ]);
}
```

### Test Helpers Available

| Helper                                                  | Description                            |
|---------------------------------------------------------|----------------------------------------|
| `$this->getJson($url)`                                  | Send GET request                       |
| `$this->postJson($url, $payload)`                       | Send POST request                      |
| `$this->putJson($url, $payload)`                        | Send PUT request                       |
| `$this->deleteJson($url)`                               | Send DELETE request                    |
| `$this->persistEntity($entity)`                         | Save entity to database                |
| `self::draftRuleEntity()`                               | Create Rule test fixture               |
| `self::draftWorkflowEntity()`                           | Create Workflow test fixture           |
| `self::assertResponseReturnsJson($expected)`            | Assert JSON response (with PHPMatcher) |
| `self::assertUnprocessableResponse($violations)`        | Assert 422 with violations             |
| `self::assertDomainEventHasBeenDispatched($eventClass)` | Assert domain event                    |

## Step 6: Update OpenAPI Specification

### File Location
`oas/openapi.yaml`

### Add Path Entry

```yaml
paths:
    /authoring/{resources}:
        post:
            tags:
                - {Resources}
            summary: Draft a new {resource}
            description: Creates a new {resource} in draft state
            operationId: draft{Resource}
            requestBody:
                required: true
                content:
                    application/json:
                        schema:
                            $ref: "#/components/schemas/Draft{Resource}Payload"
            responses:
                "201":
                    description: {Resource} drafted successfully
                    content:
                        application/json:
                            schema:
                                $ref: "#/components/schemas/{Resource}Response"
                "409":
                    description: {Resource} with this name already exists
                    content:
                        application/json:
                            schema:
                                $ref: "#/components/schemas/ErrorResponse"
                "422":
                    description: Validation error
                    content:
                        application/json:
                            schema:
                                $ref: "#/components/schemas/ValidationErrorResponse"

    /authoring/{resources}/{id}:
        get:
            tags:
                - {Resources}
            summary: Get a {resource} by ID
            description: Retrieves a single {resource} with all its details
            operationId: get{Resource}
            parameters:
                - name: id
                  in: path
                  required: true
                  description: The unique identifier of the {resource}
                  schema:
                      type: string
                      pattern: "^[a-z0-9-]+$"
                      examples:
                          - "my-{resource}-id"
            responses:
                "200":
                    description: {Resource} found and returned successfully
                    content:
                        application/json:
                            schema:
                                $ref: "#/components/schemas/{Resource}Response"
                "404":
                    description: {Resource} not found
                    content:
                        application/json:
                            schema:
                                $ref: "#/components/schemas/ErrorResponse"
```

### Add Schema Definitions

```yaml
components:
    schemas:
        Draft{Resource}Payload:
            type: object
            required:
                - name
                - description
            properties:
                name:
                    type: string
                    description: Name of the {resource}
                    minLength: 1
                    maxLength: 255
                    examples:
                        - "My {Resource}"
                description:
                    type: string
                    description: Description of the {resource}
                    minLength: 1
                    maxLength: 500
                    examples:
                        - "A detailed description"

        {Resource}Response:
            type: object
            required:
                - id
                - createdAt
                - updatedAt
                - state
                - name
                - description
            properties:
                id:
                    type: string
                    description: Unique identifier
                    examples:
                        - "my-{resource}-id"
                createdAt:
                    type: string
                    format: date-time
                    description: Creation timestamp
                    examples:
                        - "2025-12-31T12:00:00+00:00"
                updatedAt:
                    type: string
                    format: date-time
                    description: Last update timestamp
                    examples:
                        - "2025-12-31T12:00:00+00:00"
                state:
                    $ref: "#/components/schemas/DirectiveState"
                name:
                    type: string
                    description: Name
                    examples:
                        - "My {Resource}"
                description:
                    type: string
                    description: Description
                    examples:
                        - "A detailed description"
                content:
                    type:
                        - string
                        - "null"
                    description: Content body
                    examples:
                        - "The actual content"
```

### OpenAPI 3.1.0 Conventions

| Pattern                            | Usage                                  |
|------------------------------------|----------------------------------------|
| `type: ["string", "null"]`         | Nullable fields (not `nullable: true`) |
| `examples: [...]`                  | Array of examples (not `example: ...`) |
| `$ref: "#/components/schemas/..."` | Reference to schema                    |

### Existing Reusable Schemas

- `DirectiveState` - Enum: draft, published, archived
- `ErrorResponse` - Standard error response (404, 409, 500)
- `ValidationErrorResponse` - 422 validation errors
- `ValidationViolation` - Individual violation details

## Workflow Summary

1. **Controller** - Add action method to existing or new controller
2. **Payload** - Create payload class with validation constraints (POST/PUT/PATCH only)
3. **Response** - Create or reuse response classes
4. **Tests** - Create integration test class with all scenarios
5. **OpenAPI** - Add path and schema definitions
6. **QA** - Run `castor qa` to validate everything

## Checklist

- [ ] Controller action implemented with proper route attributes
- [ ] Payload class created with validation constraints (if needed)
- [ ] Response class created or reused
- [ ] Integration tests cover:
  - [ ] Success case (200/201)
  - [ ] Not found case (404)
  - [ ] Conflict case (409) if applicable
  - [ ] Validation errors (422) with data provider
- [ ] OpenAPI updated:
  - [ ] Path entry added
  - [ ] Request body schema (if POST/PUT/PATCH)
  - [ ] Response schemas
  - [ ] Error responses
- [ ] `castor qa -f` passes