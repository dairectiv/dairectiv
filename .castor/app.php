<?php

declare(strict_types=1);

namespace app;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\io;
use function Castor\run;

#[AsTask(description: 'Installs frontend dependencies')]
function install(): void
{
    io()->title('Installing frontend dependencies');
    pnpm(['install']);
}

#[AsTask(description: 'Starts frontend development server')]
function dev(): void
{
    io()->title('Starting frontend development server');
    pnpm(['dev']);
}

#[AsTask(description: 'Builds frontend for production')]
function build(): void
{
    io()->title('Building frontend for production');
    pnpm(['build']);
}

#[AsTask(description: 'Previews production build')]
function preview(): void
{
    io()->title('Previewing production build');
    pnpm(['preview']);
}

#[AsTask(description: 'Runs frontend tests')]
function test(
    #[AsOption(shortcut: 'c', description: 'Generate coverage report')]
    bool $coverage = false,
): void {
    io()->title('Running frontend tests');

    $command = $coverage ? ['test:coverage'] : ['test', 'run'];
    pnpm($command);
}

#[AsTask(description: 'Runs frontend linter')]
function lint(
    #[AsOption(shortcut: 'f', description: 'Fix linting errors')]
    bool $fix = false,
): void {
    io()->title('Running frontend linter');

    $command = $fix ? ['lint:fix'] : ['lint'];
    pnpm($command);
}

#[AsTask(description: 'Starts Storybook development server')]
function storybook(): void
{
    io()->title('Starting Storybook');
    pnpm(['storybook']);
}

#[AsTask(name: 'storybook:build', description: 'Builds Storybook for production')]
function storybook_build(): void
{
    io()->title('Building Storybook');
    pnpm(['build-storybook']);
}

#[AsTask(name: 'generate:api', description: 'Generates API client from OpenAPI specification')]
function generate_api(): void
{
    io()->title('Generating API client');
    pnpm(['generate:api']);
}

#[AsTask(description: 'Runs pnpm security audit')]
function audit(): void
{
    io()->title('Running pnpm security audit');
    pnpm(['audit']);
}

/**
 * Run a pnpm command in the app directory
 *
 * @param string[] $args
 */
function pnpm(array $args): void
{
    $command = ['pnpm', ...$args];

    run($command, context: context()->withWorkingDirectory('app'));
}
