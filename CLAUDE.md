# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Stack

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

### Docker Commands

Build the infrastructure:
```bash
castor build
```

Start the infrastructure:
```bash
castor up
```

Stop the infrastructure:
```bash
castor stop
```

Destroy the infrastructure (remove containers, volumes, networks):
```bash
castor destroy
```

View logs:
```bash
castor logs
```

List containers status:
```bash
castor ps
```

### Composer Commands

Install dependencies:
```bash
castor install
```

Update dependencies:
```bash
castor update
```

Require a dependency:
```bash
castor req <package>
castor req <package> -d  # as dev dependency
```

Remove a dependency:
```bash
castor remove <package>
```

### Database Commands

Reset database (drop, create, migrate, fixtures):
```bash
castor database:reset
castor database:reset -t       # test environment only
castor database:reset -a       # all environments
castor database:reset -f       # without fixtures
```

Drop database:
```bash
castor database:drop
castor database:drop -t        # test environment
```

Create database:
```bash
castor database:create
castor database:create -t      # test environment
```

Run migrations:
```bash
castor database:migrate
castor database:migrate -t     # test environment
```

Load fixtures:
```bash
castor database:fixtures
castor database:fixtures -t    # test environment
```

Generate migration from entity changes:
```bash
castor database:diff
castor database:diff -r        # reset all environments after
```

### Quality Assurance Commands

Run all QA tasks (rector, ecs, linter, schema, phpstan, phpunit):
```bash
castor qa
castor qa -f                   # apply fixes
castor qa -d                   # check dependencies
```

Run static analysis tools in order:
```bash
castor sa
castor static
castor sa -f                   # apply fixes
```

Run PHPStan:
```bash
castor phpstan
castor phpstan -c              # CI mode (GitHub Actions error format)
```

Run Rector:
```bash
castor rector
castor rector -f               # apply fixes
castor rector -c               # CI mode (GitHub Actions output format)
```

Run ECS (Easy Coding Standard):
```bash
castor ecs
castor ecs -f                  # apply fixes
castor ecs -c                  # CI mode (checkstyle output format)
```

Run PHPUnit:
```bash
castor test
castor phpunit
castor test -d                 # testdox format
castor test -f <filter>        # filter tests
castor test -g <groups>        # run specific groups
castor test -o                 # with coverage
```

Run linters (container, yaml):
```bash
castor lint
castor linter
castor lint -c                 # CI mode (GitHub Actions format)
```

Validate Doctrine schema:
```bash
castor schema
```

Check Composer dependencies:
```bash
castor deps
castor dependencies
```

### Symfony Commands

Clear cache:
```bash
castor cc
castor symfony:cache:clear
castor cc -t                   # test environment
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
$directive = new Directive($id, $content);
$directive->recordEvent(new DirectiveCreated($id));

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
  3. Work on the issue in this dedicated branch
  4. Commits reference the issue: `feat(dai-16): add feature`
  5. Create PR when ready (one PR per issue)

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

#### Available Workflows

**Quality Workflow** (`quality.yml`)
- **Purpose**: Run static analysis, quality checks, tests, security audits and generate code coverage reports
- **Triggers**:
  - Push to any branch, pull requests (only when PHP/config files change)
  - **Daily schedule**: Runs at 6am UTC (for security audits)
- **Watched files**:
  - `api/**/*.php` (all PHP files)
  - `api/config/**` (configuration files)
  - `api/phpstan.dist.neon`, `api/rector.php`, `api/ecs.php`, `api/phpunit.xml.dist`
  - `api/composer.json`, `api/composer.lock`, `api/symfony.lock`
  - `.castor/**`, `castor.php` (task runner)
  - `.github/workflows/quality.yml` (self-trigger on workflow changes)
- **Jobs**: This workflow contains 3 distinct jobs

**Job 1: static-analysis** (runs on all branches)
- **What it does** (steps run sequentially):
  - Validates composer.json and composer.lock
  - Installs dependencies with Composer caching
  - Restores result caches (PHPStan, Rector, ECS)
  - Runs `castor rector -c` (with GitHub Actions output format)
  - Runs `castor ecs -c` (with checkstyle output format)
  - Runs `castor lint -c` (container, yaml linters with GitHub format)
  - Runs `castor schema` (Doctrine schema validation)
  - Runs `castor phpstan -c` (with GitHub Actions error format)
  - Runs `castor test` (PHPUnit without coverage)
  - Saves result caches (even if steps fail, via `!cancelled()`)
- **Environment**: PHP 8.5 with required extensions (mbstring, xml, ctype, iconv, intl, pdo_pgsql, pgsql)
- **Cache strategy**:
  - **PHP extensions**: Uses `shivammathur/cache-extensions@v1` to cache compiled PHP extensions (key: `php-extensions-v1`)
  - Composer packages for faster dependency installation
  - **Result caches** (saved even on failure):
    - PHPStan: `phpstan-result-cache-{run_id}` → `api/var/cache/.phpstan.cache`
    - Rector: `rector-result-cache-{run_id}` → `api/var/cache/.rector.cache`
    - ECS: `ecs-result-cache-{run_id}` → `api/var/cache/.ecs.cache`
  - Uses `actions/cache/restore` + `actions/cache/save` pattern for optimal cache management
- **Output formats**: All tools use CI-friendly formats for GitHub Actions annotations

**Job 2: test** (runs on all branches)
- **What it does**:
  - Sets up PHP 8.5 on the runner with Xdebug for coverage
  - Runs `castor up --service postgres` (starts PostgreSQL with `--wait` flag)
  - Runs `castor install` (install Composer dependencies)
  - Runs `castor database:reset -t` (reset test database with migrations & fixtures)
  - Runs `castor test -o` (PHPUnit with coverage)
  - Runs `castor destroy --force` (cleanup)
- **Environment**:
  - PHP 8.5 on GitHub Actions runner with Xdebug
  - PostgreSQL 16 in Docker container (accessed via localhost:40010)
- **Cache strategy**:
  - **PHP extensions**: Uses `shivammathur/cache-extensions@v1` to cache compiled PHP extensions (key: `php-extensions-v1`)
  - Composer packages for faster dependency installation
- **Coverage**: Generated with Xdebug to `api/var/coverage/clover.xml`
- **Benefits**:
  - 100% Castor commands (consistent with local development)
  - Minimal Docker usage (only PostgreSQL)
  - Auto-wait for service ready (via docker compose `--wait` flag)
  - .env configuration is used (correct database connection)

**Job 3: security** (runs on all branches + daily schedule)
- **What it does**:
  - Installs dependencies with Composer caching
  - Runs `composer audit --no-dev` (production dependencies)
  - Runs `composer audit` (all dependencies, continues on error)
- **Environment**: PHP 8.5 with required extensions (mbstring, xml, ctype, iconv, intl)
- **Cache strategy**:
  - **PHP extensions**: Uses `shivammathur/cache-extensions@v1` to cache compiled PHP extensions (key: `php-extensions-v1`)
  - Composer packages for faster dependency installation
- **Schedule**: Runs automatically every day at 6am UTC (ignores path filters on schedule)
- **Note**: This job runs on file changes AND on daily schedule for continuous security monitoring

**Docker Build Workflow** (`docker.yml`)
- **Purpose**: Verify Docker infrastructure builds successfully
- **Triggers**: Push to any branch, pull requests (only when Docker-related files change)
- **Watched files**:
  - `compose.yaml`, `compose.yml`, `docker-compose.yaml`, `docker-compose.yml`
  - `docker/**` (all files in docker directory)
  - `.dockerignore`
  - `api/composer.json`, `api/composer.lock`, `api/symfony.lock`
  - `.github/workflows/docker.yml` (self-trigger on workflow changes)
- **What it does**:
  - Builds Docker images using Docker Buildx
  - Starts Docker Compose stack
  - Waits for services to be ready
  - Checks running containers
  - Shows logs if build fails
- **Cleanup**: Tears down containers and volumes after run

**Claude Code Review Workflow** (`claude-review.yml`)
- **Purpose**: AI-powered automated code review
- **Triggers**: Pull request events (opened, synchronize, reopened)
- **What it does**:
  - Detects changed files in the PR
  - Sets up Node.js environment
  - Installs Claude Code CLI
  - Analyzes changes using Claude AI (placeholder implementation)
- **Requirements**: ANTHROPIC_API_KEY in repository secrets
- **Permissions**: Read contents, write pull request comments
- **Note**: Currently a placeholder, requires full implementation

#### Required Repository Secrets

To enable all workflows, configure these secrets in GitHub repository settings:

- `ANTHROPIC_API_KEY`: For Claude Code Review workflow (optional, placeholder)

#### Branch Protection Rules

For the `main` branch, configure these required status checks:
- Quality / Static Analysis
- Quality / Test
- Quality / Security Audit
- Docker Build / Build Docker Images

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
- Generate migration: `castor database:diff`
- Review the migration file in `migrations/`
- Apply: `castor database:migrate` or `castor database:reset`

**7. Testing**
- Write integration tests in `tests/Integration/{BoundedContext}/`
- Use test fixtures from `tests/Fixtures/`
- Run: `castor test`

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

### Creating New Skills

When adding a new Skill:
1. Create a directory in `.claude/skills/skill-name/`
2. Add a `SKILL.md` file with YAML frontmatter and instructions
3. Document the Skill in this section
4. Commit to git so the team can use it

See [Claude Code Skills documentation](https://code.claude.com/docs/en/skills.md) for details.

## Contributing to Castor Tasks

When adding a new Castor task, you **must** document it in this file:

1. Add the task in the appropriate file under `.castor/` directory (or `castor.php` for global tasks)
2. Update the relevant section in "Development Commands" above
3. Include the command syntax and a brief description

### Castor File Structure

```
castor.php              # Global tasks and context configuration (start)
.castor/
├── composer.php        # Composer dependency commands (install, update, require, remove)
├── database.php        # Database commands (reset, drop, create, migrate, fixtures, diff)
├── docker.php          # Docker infrastructure commands (build, up, stop, destroy, logs, ps)
├── quality.php         # QA commands (qa, phpstan, rector, ecs, phpunit, lint, schema, deps)
└── symfony.php         # Symfony console commands (cache:clear)
```

### Adding a New Task

Example task structure:
```php
#[AsTask(description: 'Brief description of what the task does', aliases: ['alias'])]
function task_name(): void
{
    // Task implementation
}
```
