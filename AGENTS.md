# Repository Guidelines

## Project Overview
This repository hosts **dairectiv**, an AI enablement hub that centralizes and synchronizes AI guidance (rules, commands, skills, playbooks, subagents) into native formats for multiple dev tools. The backend is a Symfony 8 API in `api/` backed by PostgreSQL.

## Stack & Tooling
- PHP 8.4+, Symfony 8.0, Doctrine ORM
- PostgreSQL 16, RabbitMQ, Docker/Compose, FrankenPHP
- QA: PHPUnit, PHPStan, Rector, Easy Coding Standard (ECS)
- Task runner: Castor (`castor.php`, `.castor/`)
- Messaging: Symfony Messenger with AMQP transport

## Architecture

The application follows **Domain-Driven Design (DDD)** with **CQRS** and **Domain Events**.

### Bounded Contexts

Each business domain is isolated in its own Bounded Context:

- **Authoring**: Create and manage directives (rules, skills, playbooks, subagents)
- **SharedKernel**: Shared concepts (CQRS, Domain Events, Aggregate Root)

### Layered Structure

Each Bounded Context follows a 4-layer architecture:

```
{BoundedContext}/
├── Domain/          # Pure business logic (entities, value objects, events)
├── Application/     # Use cases (commands, queries, handlers)
├── Infrastructure/  # Technical implementations (Doctrine, adapters)
└── UserInterface/   # Entry points (controllers, CLI commands)
```

### CQRS Pattern

- **Commands**: Write operations that change state (void return)
- **Queries**: Read operations that return data (no side effects)
- **Buses**: CommandBus and QueryBus dispatch to handlers via Symfony Messenger
- **Handlers**: Auto-registered, implement business logic or orchestrate domain

### Domain Events

- Aggregates record events during business operations
- Events are published after successful transaction via `DomainEventMiddleware`
- Other contexts can listen and react asynchronously

## Project Structure
- `api/src/{BoundedContext}/`: each business domain
- `api/src/SharedKernel/`: CQRS infrastructure, domain events
- `api/config/`: Symfony + Messenger configuration
- `api/migrations/`: Doctrine migrations
- `api/tests/`: PHPUnit tests (fixtures, integration tests by context)
- `compose.yaml`: PostgreSQL + RabbitMQ infrastructure

## Build, Test, and Development Commands
All commands are run through Castor:
- `castor start`: full reset and bootstrap (build, up, install, DB reset)
- `castor build` / `castor up` / `castor stop` / `castor destroy`: Docker lifecycle
- `castor install` / `castor update`: Composer dependencies
- `castor database:reset`: drop/create/migrate/fixtures (`-t` for tests)
- `castor qa`: full QA pipeline (phpstan, rector, ecs, lint, schema, phpunit)
- `castor phpstan`, `castor rector`, `castor ecs`, `castor test`: run specific tools

## Coding Style & Conventions
- Indentation: 4 spaces (see `.editorconfig`)
- Use Doctrine attributes, constructor property promotion, `readonly` where relevant
- **Always** build strings with `\sprintf()` (no interpolation)
- Symfony autowiring is default; avoid manual service wiring unless needed

## Testing Guidelines
- Framework: PHPUnit (`api/tests/`)
- Run `castor test` locally; CI uses `castor test -o` for coverage
- Database-backed tests use the Docker Postgres service

## Git, Linear, and PR Workflow
- 1 Linear issue = 1 branch named `dai-XXX` (issue `DAI-16` → `dai-16`)
- Commit format: `feat(dai-16): short title` (Conventional Commits, no emojis)
- One PR per issue; link the Linear ticket and ensure Castor checks pass

## Configuration & Environment
- Default env: `api/.env`; local overrides: `.env.local`
- PostgreSQL via Docker at `localhost:40010`; `DATABASE_URL` is in `api/.env`

## Documentation & Automation Notes
- When adding Castor tasks, document them in `CLAUDE.md` under Development Commands.
- Use Context7 for library/API documentation when generating code or config steps.
