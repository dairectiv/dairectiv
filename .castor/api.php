<?php

declare(strict_types=1);

namespace api;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\exit_code;
use function Castor\io;

// =============================================================================
// Dependency Management (formerly composer.php)
// =============================================================================

/**
 * Execute a composer command in the API directory
 */
function composer(string $command): int
{
    return exit_code("composer $command", context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Installs Composer dependencies', aliases: ['install'])]
function install(): int
{
    io()->section('Installing dependencies...');

    return composer('install');
}

#[AsTask(description: 'Updates Composer dependencies', aliases: ['update'])]
function update(): int
{
    io()->section('Updating dependencies...');

    return composer('update');
}

#[AsTask(name:'require', description: 'Requires a Composer dependency', aliases: ['req'])]
function require_dependency(
    string $name,
    #[AsOption(shortcut: 'd', description: 'As dev dependency')]
    bool $dev = false,
): int {
    io()->section('Requiring dependency...');

    $command = "req $name";

    if ($dev) {
        $command .= ' --dev';
    }

    return composer($command);
}

#[AsTask(description: 'Removes a Composer dependency')]
function remove(string $name): int
{
    io()->section('Removing dependency...');

    return composer("remove $name");
}

// =============================================================================
// Symfony Commands (formerly symfony.php)
// =============================================================================

#[AsTask(name: 'cache:clear', description: 'Clears the Symfony cache', aliases: ['cc'])]
function cache_clear(
    #[AsOption(shortcut: 't', description: 'Use the test environment')]
    bool $test = false,
): int {
    io()->section('Clearing cache...');

    $command = 'bin/console cache:clear';

    if ($test) {
        $command .= ' --env test';
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

// =============================================================================
// Testing
// =============================================================================

#[AsTask(description: 'Runs PHPUnit tests', aliases: ['test'])]
function test(
    #[AsOption(shortcut: 'd', description: 'Testdox format')]
    bool $testdox = false,
    #[AsOption(description: 'Filter tests by name')]
    ?string $filter = null,
    #[AsOption(shortcut: 'g', description: 'Groups (comma-separated)')]
    ?string $groups = null,
    #[AsOption(shortcut: 'o', description: 'Generate coverage report')]
    bool $coverage = false,
): int {
    io()->section('Running PHPUnit...');

    $command = 'bin/phpunit';

    if ($testdox) {
        $command = \sprintf('%s --testdox', $command);
    }

    if ($filter !== null) {
        $command = \sprintf('%s --filter=%s', $command, $filter);
    }

    if ($groups !== null) {
        $command = \sprintf('%s --group=%s', $command, $groups);
    }

    if ($coverage) {
        $command = \sprintf('%s --coverage-clover var/coverage/clover.xml', $command);
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

// =============================================================================
// Static Analysis & Linting
// =============================================================================

#[AsTask(description: 'Runs all linters (Rector, ECS, container, YAML, schema)')]
function lint(
    #[AsOption(shortcut: 'f', description: 'Apply fixes where possible')]
    bool $fix = false,
    #[AsOption(shortcut: 'i', description: 'CI mode (GitHub Actions format)')]
    bool $ci = false,
): int {
    io()->section('Running API linters...');

    $results = [];

    // Rector
    $results[] = rector(fix: $fix, ci: $ci);
    if ($results[0] !== 0 && !$fix) {
        io()->warning('Rector found issues. Run with --fix to apply changes.');
        return $results[0];
    }

    // ECS
    $results[] = ecs(fix: $fix, ci: $ci);
    if ($results[1] !== 0 && !$fix) {
        io()->warning('ECS found issues. Run with --fix to apply changes.');
        return $results[1];
    }

    // Container & YAML linters
    $results[] = lint_container(ci: $ci);
    if ($results[2] !== 0) {
        return $results[2];
    }

    // Doctrine schema
    $results[] = lint_schema();
    if ($results[3] !== 0) {
        return $results[3];
    }

    return max($results);
}

#[AsTask(description: 'Runs PHPStan static analysis')]
function phpstan(
    #[AsOption(shortcut: 'i', description: 'CI mode (GitHub Actions format)')]
    bool $ci = false,
): int {
    io()->section('Running PHPStan...');

    $command = 'vendor/bin/phpstan analyze --configuration=phpstan.dist.neon';

    if ($ci) {
        $command = \sprintf('%s --error-format=github', $command);
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Runs Rector')]
function rector(
    #[AsOption(shortcut: 'f', description: 'Apply fixes')]
    bool $fix = false,
    #[AsOption(shortcut: 'i', description: 'CI mode (GitHub Actions format)')]
    bool $ci = false,
): int {
    io()->section('Running Rector...');

    $command = 'vendor/bin/rector process -c rector.php';

    if (!$fix) {
        $command = \sprintf('%s --dry-run', $command);
    }

    if ($ci) {
        $command = \sprintf('%s --output-format=github', $command);
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Runs ECS (Easy Coding Standard)')]
function ecs(
    #[AsOption(shortcut: 'f', description: 'Apply fixes')]
    bool $fix = false,
    #[AsOption(shortcut: 'i', description: 'CI mode (GitHub Actions format)')]
    bool $ci = false,
): int {
    io()->section('Running ECS...');

    $command = 'vendor/bin/ecs --config=ecs.php';

    if ($fix) {
        $command = \sprintf('%s --fix', $command);
    }

    if ($ci) {
        $command = \sprintf('%s --output-format=checkstyle', $command);
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

function lint_container(bool $ci = false): int
{
    $format = $ci ? ' --format=github' : '';

    return exit_code(
        \sprintf('bin/console lint:container%s', $format),
        context: context()->withWorkingDirectory('api'),
    );
}

function lint_yaml(bool $ci = false): int
{
    $format = $ci ? ' --format=github' : '';

    return exit_code(
        \sprintf('bin/console lint:yaml config%s', $format),
        context: context()->withWorkingDirectory('api'),
    );
}

function lint_schema(): int
{
    return exit_code(
        'bin/console doctrine:schema:validate --skip-sync',
        context: context()->withWorkingDirectory('api'),
    );
}

// =============================================================================
// Security Audit
// =============================================================================

#[AsTask(description: 'Runs Composer security audit')]
function audit(): int
{
    io()->section('Running Composer security audit...');

    return composer('audit --locked');
}
