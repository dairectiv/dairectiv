# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

### Backend (API)

- Docker & Docker Compose
- PostgreSQL 16
- PHP 8.5
- Symfony 8.0
- FrankenPHP
- Doctrine
- PHPUnit
- PHPStan
- Rector
- Easy Coding Standard
- Castor

### Frontend (App)

- pnpm (package manager)
- Vite 6 (build tool)
- TypeScript 5
- React 19
- TanStack Router (routing)
- TanStack Query (server state)
- Zustand (client state)
- Mantine 8 (UI components)
- Vitest (testing)
- Storybook 8 (component development)
- Biome (linting & formatting)

### Documentation

Always use context7 when I need code generation, setup or configuration steps, or library/API documentation.
This means you should automatically use the Context7 MCP tools to resolve library id and get library docs without me having to explicitly ask.

**How to read this table:** Use the exact ID from the second column when referencing a library via Context7. The "When?" column indicates the specific topics and use cases where each library's documentation should be consulted.

| Library      | ID                                  | When ?                                                                                                |
|--------------|-------------------------------------|-------------------------------------------------------------------------------------------------------|
| Symfony      | `/websites/symfony_com-doc-current` | Controllers, routing, console commands, events, dependency injection, configuration, HTTP foundation  |
| FrankenPHP   | `/websites/frankenphp_dev`          | PHP application server, workers, Caddy configuration, deployment, HTTP/2-3, performance optimization  |
| PHPStan      | `/phpstan/phpstan`                  | Static analysis configuration, custom rules, type inference, error levels, baseline management        |
| Doctrine ORM | `/doctrine/orm`                     | Entity definitions, migrations, DQL queries, repositories, associations, lifecycle callbacks          |
| PHPUnit      | `/websites/phpunit_de_en_12_4`      | Unit tests, assertions, test doubles, mocks, data providers, test lifecycle                           |
| PHP          | `/php/doc-en`                       | Native functions, standard library, language features, PHP 8.5 syntax, built-in classes               |
| Castor       | `/jolicode/castor`                  | Task automation, custom commands, build scripts, deployment workflows, CI/CD pipelines                |

## Project Overview

**dairectiv** is an AI enablement hub for engineering teams that provides a single source of truth for authoring, versioning, and governing AI guidance (rules, commands, skills, playbooks, subagents). It syncs this guidance into native formats for various AI dev tools (AGENTS.md, Cursor rules, Claude Code, JetBrains AI Assistant, OpenAI Codex).

The repository contains a Symfony 8 API backend (`api/`) with PostgreSQL database support.

## Development Commands

All commands are managed via [Castor](https://castor.jolicode.com/). Run `castor list` to see all available commands.

### Quick Start

Reset and start the full environment (destroy, build, up, install dependencies, reset database):
```bash
castor start
```

### Infrastructure Commands (infra:)

Build the infrastructure:
```bash
castor infra:build
castor build                   # alias
```

Start the infrastructure:
```bash
castor infra:up
castor up                      # alias
```

Stop the infrastructure:
```bash
castor infra:down
castor down                    # alias
```

Destroy the infrastructure (remove containers, volumes, networks):
```bash
castor infra:destroy
castor destroy                 # alias
```

View logs:
```bash
castor infra:logs
castor logs                    # alias
```

List containers status:
```bash
castor infra:ps
castor ps                      # alias
```

### API Commands (api:)

Install Composer dependencies:
```bash
castor api:install
castor install                 # alias
```

Update Composer dependencies:
```bash
castor api:update
castor update                  # alias
```

Require a Composer dependency:
```bash
castor api:require <package>
castor req <package>           # alias
castor api:require <package> -d  # as dev dependency
```

Remove a Composer dependency:
```bash
castor api:remove <package>
```

Clear Symfony cache:
```bash
castor api:cache:clear
castor cc                      # alias
castor cc -t                   # test environment
```

Run PHPUnit tests:
```bash
castor api:test
castor test                    # alias
castor api:test -d             # testdox format
castor api:test --filter <name>  # filter tests
castor api:test -g <groups>    # run specific groups
castor api:test -c             # with coverage
```

Run all API linters (Rector, ECS, container, YAML, schema):
```bash
castor api:lint
castor api:lint -f             # apply fixes
```

Run PHPStan:
```bash
castor api:phpstan
castor phpstan                 # legacy alias
castor phpstan --ci            # CI mode (GitHub Actions format)
```

Run Rector:
```bash
castor api:rector
castor rector                  # legacy alias
castor rector -f               # apply fixes
castor rector --ci             # CI mode (GitHub Actions format)
```

Run ECS (Easy Coding Standard):
```bash
castor api:ecs
castor ecs                     # legacy alias
castor ecs -f                  # apply fixes
castor ecs --ci                # CI mode (checkstyle format)
```

Run Composer security audit:
```bash
castor api:audit
```

### Database Commands (db:)

Reset database (drop, create, migrate, fixtures):
```bash
castor db:reset
castor database:reset          # legacy alias
castor db:reset -t             # test environment only
castor db:reset -a             # all environments
castor db:reset -f             # without fixtures
```

Drop database:
```bash
castor db:drop
castor database:drop           # legacy alias
castor db:drop -t              # test environment
```

Create database:
```bash
castor db:create
castor database:create         # legacy alias
castor db:create -t            # test environment
```

Run migrations:
```bash
castor db:migrate
castor database:migrate        # legacy alias
castor db:migrate -t           # test environment
```

Load fixtures:
```bash
castor db:fixtures
castor database:fixtures       # legacy alias
castor db:fixtures -t          # test environment
```

Generate migration from entity changes:
```bash
castor db:diff
castor database:diff           # legacy alias
castor db:diff -r              # reset all environments after
```

### App Commands (app:)

Install frontend dependencies:
```bash
castor app:install
```

Start development server:
```bash
castor app:dev
```

Build for production:
```bash
castor app:build
```

Preview production build:
```bash
castor app:preview
```

Run frontend tests:
```bash
castor app:test
castor app:test -c             # with coverage
```

Run frontend linter:
```bash
castor app:lint
castor app:lint -f             # fix errors
```

Start Storybook:
```bash
castor app:storybook
castor app:storybook:build     # build for production
```

Generate API client from OpenAPI:
```bash
castor app:generate:api
```

Run pnpm security audit:
```bash
castor app:audit
```

### Quality Assurance Commands (qa:)

Run all QA tasks (API + App):
```bash
castor qa:all
castor qa                      # alias
castor qa -f                   # apply fixes
```

Run API QA tasks (lint + phpstan + test):
```bash
castor qa:api
castor qa:api -f               # apply fixes
```

Run App QA tasks (lint + test):
```bash
castor qa:app
castor qa:app -f               # apply fixes
```

### OpenAPI Commands (oas:)

Lint OpenAPI specification with Spectral:
```bash
castor oas:lint
castor oas:lint --ci           # GitHub Actions format
```

## Architecture

The application follows **Domain-Driven Design (DDD)** principles with clear **Bounded Contexts** and **CQRS** patterns.

### Core Principles

- **Bounded Contexts**: Business domains are separated into independent contexts
- **CQRS**: Commands (write) and Queries (read) are separated for clarity
- **Domain Events**: Aggregates emit events for cross-context communication
- **Hexagonal Architecture**: Domain layer is independent from infrastructure

### Directory Structure

```
api/
├── bin/                     # CLI executables (console)
├── config/                  # Configuration files
│   ├── packages/            # Bundle configurations (doctrine, messenger, framework)
│   ├── routes/              # Routing configuration
│   ├── bundles.php          # Registered bundles
│   ├── services.yaml        # Service container config
│   └── services_test.yaml   # Test environment services
├── migrations/              # Doctrine database migrations
├── public/                  # Web server document root
│   └── index.php            # Application entry point
├── src/
│   ├── Authoring/           # Bounded Context: Directive authoring
│   │   ├── Domain/          # Business logic, entities, value objects
│   │   ├── Application/     # Use cases, commands, queries
│   │   ├── Infrastructure/  # Doctrine repositories, adapters
│   │   └── UserInterface/   # HTTP controllers, CLI commands
│   │
│   └── SharedKernel/        # Shared concepts across contexts
│       ├── Domain/          # Domain events, aggregate root
│       ├── Application/     # CQRS interfaces (Command, Query, Bus)
│       └── Infrastructure/
│           ├── Symfony/     # Symfony integration (Kernel, Messenger)
│           └── Zenstruck/   # Test fixtures (Foundry)
├── tests/
│   ├── Fixtures/            # Test data (commands, queries, aggregates)
│   ├── Framework/           # Base test classes
│   └── Integration/         # Integration tests by bounded context
├── var/                     # Generated files (cache, logs)
└── vendor/                  # Composer dependencies
```

### Frontend Directory Structure

The frontend follows a **feature-first architecture** organized by bounded context and entity:

```
app/
├── .storybook/              # Storybook configuration
├── public/                  # Static assets
├── src/
│   ├── {bounded-context}/   # e.g., authoring/
│   │   └── {entity}/        # e.g., rule/
│   │       └── {feature}/   # e.g., list/, create/, edit/
│   │           ├── components/   # Presentational components
│   │           ├── hooks/        # Data fetching & state logic
│   │           ├── pages/        # Page components (composition)
│   │           ├── routes/       # Route definitions
│   │           ├── __tests__/    # Tests for all layers
│   │           └── index.ts      # Barrel export
│   │
│   ├── home/                # Home page feature
│   │   └── pages/
│   │
│   ├── shared-kernel/       # Shared across bounded contexts
│   │   ├── domain/          # Shared types, errors, schemas
│   │   ├── infrastructure/  # API client, query client, stores
│   │   └── ui/              # Layouts, design system components
│   │
│   ├── router.tsx           # Code-based router configuration
│   └── main.tsx             # Application entry point
│
├── tests/                   # Test utilities and mocks
├── biome.json               # Biome configuration
├── openapi-ts.config.ts     # Hey-API configuration
├── tsconfig.json            # TypeScript configuration
└── vite.config.ts           # Vite configuration
```

### Feature Structure Example

```
src/authoring/rule/list/
├── components/
│   ├── rules-list.tsx           # Main list component
│   ├── rules-list-toolbar.tsx   # Search, filter, sort controls
│   └── rules-list-empty.tsx     # Empty state
├── hooks/
│   └── use-rules-list.ts        # Data fetching + URL state
├── pages/
│   └── rules-list.page.tsx      # Composition layer
├── routes/
│   └── rules-list.route.ts      # Route + search params schema
├── __tests__/
│   └── ...
└── index.ts                     # Barrel export (all public APIs)
```

### Frontend Routing

The application uses **code-based routing** with TanStack Router:

- Routes are defined in feature `routes/` folders
- All routes are registered in `src/router.tsx`
- Search params use Zod schemas for validation
- URL state for filters, pagination, sorting (shareable links)

### Frontend State Management

| State Type | Tool | Use Case |
|------------|------|----------|
| **URL State** | TanStack Router | Filters, pagination, sorting |
| **Server State** | TanStack Query | API data fetching, caching |
| **Client State** | Zustand | UI state (modals, sidebar toggle) |

### Frontend Import Pattern

Use **barrel exports** for all internal imports:

```typescript
// Good - barrel export
import { RulesList, useRulesList, type RulesListStateFilter } from "@/authoring/rule/list";

// Bad - direct file path
import { RulesList } from "@/authoring/rule/list/components/rules-list";
```

### Bounded Contexts

Each bounded context follows the same layered structure:

**Domain Layer** (`Domain/`)
- Contains business logic, entities, value objects, domain events
- Independent from infrastructure and frameworks
- Example: `Directive` aggregate, `DirectiveId` value object

**Application Layer** (`Application/`)
- Orchestrates use cases via Commands and Queries
- Example: `CreateDirectiveCommand`, `GetDirectiveQuery`

**Infrastructure Layer** (`Infrastructure/`)
- Technical implementations: repositories (Doctrine), adapters
- Example: `DoctrineDirectiveRepository`

**UserInterface Layer** (`UserInterface/`)
- Entry points: HTTP controllers, CLI commands
- Example: `CreateDirectiveController`

### CQRS Pattern

The application uses **Command Query Responsibility Segregation**:

**Commands** (write operations)
- Implement `SharedKernel\Application\Command\Command`
- Handled by `CommandHandler`
- Dispatched via `CommandBus` (Symfony Messenger)
- Example: `CreateDirectiveCommand` → `CreateDirectiveHandler`

**Queries** (read operations)
- Implement `SharedKernel\Application\Query\Query`
- Handled by `QueryHandler`
- Dispatched via `QueryBus` (Symfony Messenger)
- Example: `GetDirectiveQuery` → `GetDirectiveHandler`

### Domain Events

Aggregates can emit domain events for cross-context communication:

```php
// Aggregate emits event
$directive = new Directive($value, $content);
$directive->recordEvent(new DirectiveCreated($value));

// Messenger middleware publishes events after transaction
// Other bounded contexts can listen via event handlers
```

### Messenger Configuration

- **Transports**: RabbitMQ (AMQP) for async processing, Test transport for integration tests
- **Middleware**: `DomainEventMiddleware` publishes aggregate events after command/query handling
- **Routing**: Commands and Queries routed to sync transport by default

### Database Configuration

The application connects to PostgreSQL via Docker:
- **Host**: localhost:40010 (mapped from container's 5432)
- **Database**: dairectiv
- **User**: dairectiv
- **Connection string**: Defined in `api/.env` as `DATABASE_URL`

### Symfony Configuration

- **Autowiring**: Enabled by default for all services in `Dairectiv\` namespace
- **Public services**: All services are public by default for testing
- **Domain exclusion**: Domain layers are excluded from autowiring (pure business logic)
- **Routing**: Uses PHP attributes (`#[Route]`) for controller methods
- **Entity Mapping**: Uses Doctrine attributes for entity definitions
- **Naming Strategy**: `underscore_number_aware` for database table/column names

## Development Workflow

### Git & Linear Workflow

**1 Linear Issue = 1 Git Branch**

The project follows a strict one-to-one relationship between Linear issues and git branches:

- **Branch naming**: `dai-XXX` where XXX is the Linear issue number
  - Example: Issue `DAI-16` → Branch `dai-16`
  - The branch name must match the Linear issue identifier (lowercase)

- **Workflow**:
  1. Create or assign yourself a Linear issue
  2. Create a branch named after the issue: `git checkout -b dai-XXX`
  3. **Set the Linear issue status to "In Progress"** when starting work
  4. Work on the issue in this dedicated branch
  5. Commits reference the issue: `feat(dai-16): add feature`
  6. Create PR when ready (one PR per issue)
  7. **Never manually set an issue to "Done"** - GitHub automation handles this when the PR is merged (via `Closes DAI-XXX` in PR description)

- **Benefits**:
  - Clear traceability: branch → commits → PR → issue
  - Git commit skill automatically fetches Linear issue context
  - Easy to understand what each branch is for
  - Simplifies code review and release notes

- **Special branches**:
  - `main`: Production-ready code
  - `develop`: Integration branch (if using git-flow)
  - Branches without `dai-XXX` pattern are for infrastructure/tooling work

### CI/CD Pipeline

The project uses GitHub Actions for continuous integration and deployment. All workflows are located in `.github/workflows/`.

#### Unified CI Workflow (`ci.yml`)

The CI workflow is organized into stages with proper dependencies:

```
BUILD STAGE (parallel)
  ├── docker          → Build Docker images, verify services
  ├── api-deps        → Install Composer dependencies (cached)
  ├── app-deps        → Install pnpm dependencies (cached)
  ├── app-build       → Build app for production (needs: app-deps)
  └── storybook-build → Build Storybook (needs: app-deps)

TEST STAGE (needs: build)
  ├── api-static-analysis → Rector, ECS, linters, PHPStan (needs: api-deps)
  ├── app-static-analysis → Biome lint, TypeScript check (needs: app-deps)
  ├── api-test            → PHPUnit with coverage (needs: api-deps)
  ├── app-test            → Vitest with coverage (needs: app-deps)
  ├── api-deps-audit      → Composer security audit (needs: api-deps)
  ├── app-deps-audit      → pnpm security audit (needs: app-deps)
  └── oas-lint            → OpenAPI Spectral linting

REVIEW STAGE (disabled, needs: test)
  └── claude-code-review  → AI-powered code review
```

**Triggers:**
- Push to any branch
- Watched paths: `api/**`, `app/**`, `oas/**`, `docker/**`, `compose.yaml`, `.castor/**`, `castor.php`, `.spectral.yaml`

**Key features:**
- **Dependency-aware**: Jobs only run if their dependencies succeed
- **Artifact sharing**: Build stage uploads artifacts (vendor, node_modules) for test stage
- **Concurrency control**: Cancels in-progress runs for the same branch
- **Caching**: PHP extensions, Composer packages, pnpm modules, PHPStan/Rector/ECS results

**Claude Code Interactive Workflow** (`claude.yml`)
- **Purpose**: Interactive Claude Code assistance via GitHub comments
- **Triggers**: Issue/PR comments containing `@claude`
- **Requirements**: `CLAUDE_CODE_OAUTH_TOKEN` in repository secrets

#### Branch Protection Rules

For the `main` branch, configure these required status checks:
- `Build / Docker Images`
- `Build / API Dependencies`
- `Build / App Dependencies`
- `Build / App Production Build`
- `Test / API Static Analysis`
- `Test / API Unit & Integration Tests`
- `Test / API Security Audit`
- `Test / App Static Analysis`
- `Test / App Unit Tests`
- `Test / App Security Audit`
- `Test / OpenAPI Lint`

This ensures all quality gates pass before merging to main.

### DDD Development Workflow

When implementing new features, follow the DDD approach:

**1. Identify the Bounded Context**
- Determine which context owns the feature (Authoring, etc.)
- If unsure, discuss with the team

**2. Start with the Domain**
- Define entities, value objects, and aggregates
- Write domain events if needed
- Keep domain logic pure (no framework dependencies)

**3. Add Application Layer**
- Create Commands for write operations
- Create Queries for read operations
- Implement handlers that orchestrate domain logic

**4. Infrastructure Implementation**
- Implement repositories with Doctrine
- Configure Messenger routing if needed

**5. UserInterface Layer**
- Create HTTP controllers or CLI commands
- Use CommandBus/QueryBus to dispatch use cases

**6. Database Migrations**
- Generate migration: `castor db:diff`
- Review the migration file in `migrations/`
- Apply: `castor db:migrate` or `castor db:reset`

**7. Testing**
- Write integration tests in `tests/Integration/{BoundedContext}/`
- Use test fixtures from `tests/Fixtures/`
- Run: `castor api:test`

## Issue Tracking Workflow

This project uses Linear for issue tracking. See `AGENTS.md` for detailed templates and workflow:

- **Issue Types**: Feature, Bugfix, Improvement, Chore, Spike, Documentation
- **Process**: Select project → Choose template → Fill description → Create via MCP
- **Documentation follows Diataxis**: Tutorial, How-to, Reference, Explanation

When creating Linear issues via MCP, use structured templates with clear context, goals, and success criteria. Documentation should follow the Diataxis framework and be placed in appropriate subdirectories under `docs/`.

## Environment Files

- `.env`: Main environment configuration (tracked, contains defaults)
- `.env.dev`: Development-specific overrides
- `.env.local`: Local overrides (not tracked, for personal settings)

Key environment variables:
- `APP_ENV`: Environment (dev, prod, test)
- `APP_SECRET`: Symfony secret key
- `APP_SHARE_DIR`: Shared directory path (default: `var/share`)
- `DATABASE_URL`: PostgreSQL connection string
- `DEFAULT_URI`: Default application URI

## DDD Development Guidelines

**Architecture Patterns**
- Follow DDD principles: Bounded Contexts, Aggregates, Value Objects, Domain Events
- Use CQRS: separate Commands (write) and Queries (read)
- Keep Domain layer pure: no framework dependencies, only business logic
- Use Hexagonal Architecture: Domain → Application → Infrastructure → UserInterface

**Code Organization**
- Place new features in the appropriate Bounded Context (`Authoring/`, etc.)
- Follow the 4-layer structure: Domain, Application, Infrastructure, UserInterface
- Domain objects go in `{Context}/Domain/`, use cases in `{Context}/Application/`
- Keep SharedKernel minimal: only truly shared concepts

**CQRS Implementation**
- Commands change state, return void: `CreateDirectiveCommand`
- Queries return data, never modify state: `GetDirectiveQuery`
- Use CommandBus/QueryBus to dispatch, never call handlers directly
- Handlers are auto-registered via Symfony Messenger

**Domain Events**
- Aggregates extend `SharedKernel\Domain\AggregateRoot`
- Use `recordEvent()` to emit domain events
- Events are published automatically by `DomainEventMiddleware` after transaction
- Other contexts can listen via Messenger event handlers

**Technical Standards**
- PHP 8.4+ features: constructor property promotion, readonly properties
- Doctrine ORM 3.x with attributes (not annotations)
- Symfony autowiring enabled (except Domain layer)
- Always use `\sprintf()` for string formatting (no interpolation)

## PHP Coding Standards

### String Formatting

Always use `\sprintf()` instead of string interpolation for building strings with variables:

```php
// Good
$message = \sprintf('Hello %s, you have %d messages', $name, $count);

// Bad
$message = "Hello $name, you have $count messages";
$message = "Hello {$name}, you have {$count} messages";
```

## TypeScript Coding Standards

### Naming Conventions

| Element       | Convention          | Example                           |
|---------------|---------------------|-----------------------------------|
| **Folders**   | kebab-case          | `rule-card/`, `shared-kernel/`    |
| **Files**     | kebab-case          | `rule-card.tsx`, `use-rules.ts`   |
| **Components**| PascalCase (in code)| `function RuleCard()`             |
| **Hooks**     | camelCase with use  | `useRules`, `useRuleMutations`    |
| **Types**     | PascalCase          | `Rule`, `RuleFormValues`          |
| **Schemas**   | camelCase           | `ruleSchema`, `createRuleSchema`  |

### Path Aliases

Use path aliases for imports instead of relative paths:

```typescript
// Good
import { queryClient } from "@shared/infrastructure/query-client/query-client";
import { ApiError } from "@shared/domain/errors";

// Bad
import { queryClient } from "../../../shared-kernel/infrastructure/query-client/query-client";
```

Available aliases:
- `@/*` -> `src/*`
- `@shared/*` -> `src/shared-kernel/*`

### Notification System

The application uses a centralized notification system for user feedback. All notifications should be created using the helper functions from `@shared/ui/feedback/notification`.

**When to use notifications:**
- Form submissions (create, update, delete operations)
- API operations with user-visible side effects
- Error feedback from API calls
- Success confirmations for user actions

**Available helpers:**

| Function | Use Case |
|----------|----------|
| `showSuccess({ title, message })` | Direct success notification |
| `showError({ title, message })` | Direct error notification |
| `showInfo({ title, message })` | Informational notification |
| `showWarning({ title, message })` | Warning notification |
| `showLoadingNotification({ title, message, loadingMessage })` | Loading state that transforms to success/error |
| `updateToSuccess(id, { title, message })` | Update loading notification to success |
| `updateToError(id, { title, message })` | Update loading notification to error |
| `hideNotification(id)` | Hide specific notification |
| `hideAllNotifications()` | Hide all notifications |

**Pattern for mutations with loading state:**

Use the loading→success/error pattern for all API mutations to provide smooth user feedback:

```typescript
import {
  showLoadingNotification,
  updateToError,
  updateToSuccess,
} from "@shared/ui/feedback/notification";

interface MutationContext {
  notificationId: string;
}

const mutation = useMutation({
  mutationFn: async (payload) => {
    // API call
  },
  onMutate: (): MutationContext => {
    const notificationId = showLoadingNotification({
      title: "Creating rule",
      message: "Rule created successfully",
      loadingMessage: "Creating your rule...",
    });
    return { notificationId };
  },
  onSuccess: (_data, _variables, context) => {
    updateToSuccess(context.notificationId, {
      title: "Rule created",
      message: "Your rule has been created successfully.",
    });
    // Invalidate queries, navigate, etc.
  },
  onError: (error: AxiosError, _variables, context) => {
    const status = error.response?.status;

    if (status === 404) {
      updateToError(context?.notificationId ?? "", {
        title: "Not found",
        message: "The resource does not exist.",
      });
    } else {
      updateToError(context?.notificationId ?? "", {
        title: "Error",
        message: "An unexpected error occurred.",
      });
    }
  },
});
```

**Exception: Instant operations**

For operations that don't need a loading state (e.g., drag-and-drop reordering), use direct `showError()` for errors only:

```typescript
onError: (error) => {
  showError({
    title: "Error",
    message: "Failed to move item.",
  });
};
```

## Claude Code Skills

This project includes custom Skills that extend Claude's capabilities:

### Available Skills

**[linear-issue](`.claude/skills/linear-issue/`)** 
- Automatically creates structured Linear issues following team conventions
- Supports all issue types: Feature, Bugfix, Improvement, Chore, Spike, Documentation
- Provides templates and guides the workflow
- Triggered when you ask to create a Linear ticket/issue or report bugs/features

To use: Simply ask Claude to "create a Linear issue for [description]" and the Skill will guide you through the process.

**[git-commit](`.claude/skills/git-commit/`)** 
- Generates well-structured Git commit messages following Conventional Commits format
- Synchronized with Linear issue labels for consistency
- Supports all commit types: feat, fix, refactor, perf, docs, test, chore, style, build, ci, revert, spike
- **Automatically fetches Linear issue** from branch name (dai-XXX pattern) for context
- Uses Linear issue title and labels to suggest better commit messages
- **Handles mixed changes** by proposing multiple atomic commits (feat, test, docs separately)
- Triggered when you ask to commit changes or create a commit message
- Format: `<type>[(dai-XXX)]: <title>` (no emojis)

To use: Simply ask Claude to "commit these changes" or "create a commit message" and the Skill will analyze the changes, fetch the Linear issue context, and suggest appropriate message(s).

**[aggregate-root](`.claude/skills/aggregate-root/`)**
- Provides patterns for implementing DDD Aggregate Roots in the dairectiv codebase
- Includes templates for: aggregate classes, value objects (Id, Version, State, Change), domain events, exceptions
- Documents the `AggregateRootAssertions` trait for testing (enforces asserting all domain events)
- Covers optimistic locking via version comparison and conflict exceptions
- Triggered when creating new aggregates or extending existing ones

To use: Reference when implementing new aggregates. Follow the directory structure `src/{BoundedContext}/Domain/{Aggregate}/` and use the provided class signatures.

**[api-endpoint](`.claude/skills/api-endpoint/`)**
- Generates REST API endpoints following project conventions
- Covers: Controller, Payload (validation), Response classes
- Includes integration test templates with data providers for validation
- Documents OpenAPI 3.1.0 specification patterns
- Supports all HTTP methods: GET, POST, PUT, PATCH, DELETE
- Maps domain exceptions to HTTP status codes (404, 409, 422)
- Triggered when creating new API endpoints or routes

To use: Ask Claude to "create an endpoint for [resource/action]" and the Skill will guide through implementing controller, tests, and OpenAPI spec.

**[pr-review](`.claude/skills/pr-review/`)**
- Handles PR review comments and applies requested fixes
- Fetches comments via `gh pr view` and `gh api` for inline comments
- Analyzes comment types: fix, improvement, question, nitpick, praise
- Filters already processed comments to avoid duplicate fixes
- Provides a summary of addressed, resolved, and pending comments
- Triggered when you say "I commented on the PR" or mention PR feedback

To use: Simply tell Claude "I commented on the PR" or "check the PR comments" and the Skill will fetch, analyze, and apply the requested fixes.

**[repository](`.claude/skills/repository/`)**
- Documents the Port & Adapters pattern for repositories
- Interface in Domain layer, implementation in Infrastructure layer
- Covers PHPStan RepositoryMethodRule: `get*` throws exception, `find*` returns nullable
- Includes naming conventions and testing patterns

To use: Reference when implementing new repositories or extending existing ones.

**[value-object](`.claude/skills/value-object/`)**
- Patterns for implementing Value Objects following DDD principles
- Base classes: StringValue, UuidValue, IntValue, ObjectValue
- Factory methods, immutability, equality by value
- Doctrine type creation and entity mapping examples

To use: Reference when creating new value objects like IDs, descriptions, or complex domain types.

**[domain-event](`.claude/skills/domain-event/`)**
- Implements domain events for cross-context communication
- Events are immutable records of domain changes
- Naming: events use past tense (DirectiveDrafted), listeners use Listener suffix
- Integration with Symfony Messenger and AggregateRoot

To use: Reference when adding domain events to aggregates or creating event listeners.

**[doctrine-mapping](`.claude/skills/doctrine-mapping/`)**
- Entity/aggregate mapping conventions with Doctrine ORM
- PHP 8.4 asymmetric visibility (`public private(set)`)
- Relationships, inheritance (Single Table), custom types
- Uses `underscore_number_aware` naming strategy

To use: Reference when mapping entities, configuring relationships, or adding custom types.

**[exception-handler](`.claude/skills/exception-handler/`)**
- Exception hierarchy: DomainException, EntityNotFoundException, InvalidArgumentException
- HTTP status code mapping (404, 409, 422)
- Entity-specific exceptions with factory methods (`fromId()`)
- Testing exception scenarios

To use: Reference when creating exceptions or handling error cases in controllers.

**[assertions](`.claude/skills/assertions/`)**
- Guide for webmozart/assert usage in domain validation
- Project extends Assert to throw domain InvalidArgumentException (→ HTTP 422)
- Custom `kebabCase()` assertion
- Usage patterns in Value Objects and Aggregates

To use: Reference when adding validation to value objects or domain methods.

**[phpstan](`.claude/skills/phpstan/`)**
- PHPStan best practices and fixing errors properly
- CRITICAL: Never modify phpstan.dist.neon, never use @phpstan-ignore
- Custom rules: TestNameRule, UseCaseRule, RepositoryMethodRule
- PHPDoc patterns for complex types

To use: Reference when encountering PHPStan errors or understanding project-specific rules.

**[use-case](`.claude/skills/use-case/`)**
- CQRS use case implementation patterns
- Commands (write), Queries (read), Handlers
- Directory structure: `{Context}/Application/{Aggregate}/{UseCase}/`
- Integration testing with domain event assertions

To use: Reference when creating new Commands, Queries, or their Handlers.

**[api-testing](`.claude/skills/api-testing/`)**
- Integration tests for HTTP API endpoints
- Helper methods: postJson, getJson, putJson, deleteJson
- Assertions: assertResponseReturnsJson, assertUnprocessableResponse
- DataProvider pattern for validation testing
- CRITICAL: All domain events must be asserted

To use: Reference when writing endpoint tests, validation tests, or testing JSON responses.

**[frontend-feature](`.claude/skills/frontend-feature/`)**
- Complete guide for implementing frontend features
- Feature-first structure: components/, hooks/, pages/, routes/
- Patterns for lists with pagination, filtering, sorting
- URL state management with TanStack Router
- Testing patterns for hooks and components

To use: Reference when creating new pages, lists, or CRUD features in the frontend.

**[frontend-imports](`.claude/skills/frontend-imports/`)**
- Guide for organizing imports using barrel exports
- Each feature has an `index.ts` exporting all public APIs
- Use `@/` and `@shared/` path aliases
- Cross-feature imports use barrel export path

To use: Reference when creating or refactoring feature modules, or organizing exports.

**[app-testing](`.claude/skills/app-testing/`)**
- Complete guide for frontend testing
- Unit tests with Vitest for hooks and utilities
- Component tests with React Testing Library
- Visual/E2E tests with Playwright MCP
- Storybook URL: http://127.0.0.1:6006, App URL: http://127.0.0.1:3000
- Test commands: `castor app:test`, `castor app:test -c` (with coverage)

To use: Reference when writing tests for hooks, components, or pages, or when debugging UI issues with Playwright.

### Creating New Skills

When adding a new Skill:
1. Create a directory in `.claude/skills/skill-name/`
2. Add a `SKILL.md` file with YAML frontmatter and instructions
3. Document the Skill in this section
4. Commit to git so the team can use it

See [Claude Code Skills documentation](https://code.claude.com/docs/en/skills.md) for details.

## Claude Code Subagents

This project includes custom Subagents for autonomous task execution:

### Available Subagents

**[spec-driven-development](`.claude/agents/spec-driven-development.md`)**
- Generic development agent following specification-driven workflow
- Reads specifications from: Linear issues, markdown files, or user descriptions
- Detects task type and uses appropriate skills automatically
- Full workflow: intake → analyze → implement → migrate → QA → commit → push → PR
- Uses all available Skills based on context

To use: Ask Claude to "implement DAI-XXX" or "implement the spec in docs/feature.md"

### Creating New Subagents

When adding a new Subagent:
1. Create a file in `.claude/agents/agent-name.md`
2. Add YAML frontmatter with name, description, model, and color
3. Define the agent's workflow and capabilities
4. Document the Subagent in this section
5. Commit to git so the team can use it

## Contributing to Castor Tasks

When adding a new Castor task, you **must** document it in this file:

1. Add the task in the appropriate file under `.castor/` directory (or `castor.php` for global tasks)
2. Update the relevant section in "Development Commands" above
3. Include the command syntax and a brief description

### Castor File Structure

```
castor.php              # Global tasks and context configuration (start)
.castor/
├── api.php             # API commands (install, update, require, remove, cache:clear, test, lint, phpstan, rector, ecs, audit)
├── app.php             # App commands (install, dev, build, preview, test, lint, storybook, generate:api, audit)
├── db.php              # Database commands (reset, drop, create, migrate, fixtures, diff)
├── infra.php           # Infrastructure commands (build, up, down, destroy, logs, ps)
└── qa.php              # QA orchestration (all, api, app) + legacy aliases (rector, ecs, phpstan) + oas:lint
```

### Option Shortcut Conventions

| Shortcut | Meaning                          |
|----------|----------------------------------|
| `-t`     | Test environment                 |
| `-f`     | Fix/apply changes                |
| `-i`     | CI mode (GitHub Actions format)  |
| `-o`     | Coverage output                  |
| `-a`     | All environments                 |
| `-d`     | Dev dependency (for api:require) |
| `-g`     | Groups (for api:test)            |
| `-r`     | Reset (for db:diff)              |

### Adding a New Task

Example task structure:
```php
#[AsTask(description: 'Brief description of what the task does', aliases: ['alias'])]
function task_name(): void
{
    // Task implementation
}
```
