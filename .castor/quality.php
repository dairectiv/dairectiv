<?php

declare(strict_types=1);

namespace quality;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\exit_code;
use function Castor\io;
use function composer\update;

#[AsTask(description: 'Runs all QA tasks', aliases: ['qa'])]
function all(
    #[AsOption(shortcut: 'f', description: 'Apply changes to files')]
    bool $fix = false,
    #[AsOption(shortcut: 'd', description: 'Check dependencies')]
    bool $withDeps = false,
): int {
    $deps = 0;
    $update = 0;

    if ($withDeps) {
        $deps = dependencies();

        if ($deps !== 0) {
            $update = update();
        }
    }

    return max(
        $deps === 0 ? 0 : $update,
        rector($fix),
        ecs($fix),
        linter(),
        schema(),
        phpstan(),
        phpunit(),
    );
}

#[AsTask(description: 'Runs PHPStan', aliases: ['phpstan'])]
function phpstan(): int
{
    io()->section('Running PHPStan...');

    return exit_code('vendor/bin/phpstan analyze --configuration=phpstan.dist.neon', context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Runs Rector', aliases: ['rector'])]
function rector(
    #[AsOption(shortcut: 'f', description: 'Apply changes to files')]
    bool $fix = false,
): int
{
    io()->section('Running Rector...');

    $command = 'vendor/bin/rector process -c rector.php';

    if (!$fix) {
        $command = \sprintf('%s --dry-run', $command);
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Runs ECS', aliases: ['ecs'])]
function ecs(
    #[AsOption(shortcut: 'f', description: 'Apply changes to files')]
    bool $fix = false,
): int
{
    io()->section('Running ECS...');

    $command = 'vendor/bin/ecs --config=ecs.php';

    if ($fix) {
        $command = \sprintf('%s --fix', $command);
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Runs PHPUnit', aliases: ['phpunit', 'test'])]
function phpunit(
    #[AsOption(shortcut: 'd', description: 'Testdox format')]
    bool $testdox = false,
    #[AsOption(shortcut: 'f', description: 'Filter')]
    ?string $filter = null,
    #[AsOption(shortcut: 'g', description: 'Groups (comma-separated)')]
    ?string $groups = null,
    #[AsOption(shortcut: 'o', description: 'With coverage')]
    bool $coverage = false,
): int
{
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
        $command = \sprintf('%s --coverage-xml var/cache/coverage', $command);
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Runs static analysis tools in order', aliases: ['static', 'sa'])]
function static_analysis(
    #[AsOption(shortcut: 'f', description: 'Apply fixes where possible')]
    bool $fix = false,
): int
{
    io()->section('Running static analysis tools...');

    $results = [
        rector($fix),
    ];

    if ($results[0] !== 0 && !$fix) {
        io()->warning('Rector found issues. Run with --fix to apply changes.');
        return $results[0];
    }

    $results[] = ecs($fix);

    if ($results[1] !== 0 && !$fix) {
        io()->warning('ECS found issues. Run with --fix to apply changes.');
        return $results[1];
    }

    $results[] = linter();

    if ($results[2] !== 0) {
        return $results[2];
    }

    $results[] = schema();

    if ($results[3] !== 0) {
        return $results[3];
    }

    $results[] = phpstan();

    return max($results);
}

#[AsTask(description: 'Runs Composer checks', aliases: ['dependencies', 'deps'])]
function dependencies(): int
{
    io()->section('Running Composer checks...');

    $commands = [
        'composer validate --check-lock --strict',
        'composer outdated --direct --strict --minor-only --locked',
        'composer outdated --direct --strict --patch-only --locked',
        'composer check-platform-reqs --lock',
        'composer audit --locked',
    ];

    return max(array_map(
        static fn (string $command): int => exit_code($command, context: context()->withWorkingDirectory('api')),
        $commands,
    ));
}

#[AsTask(description: 'Runs Linters', aliases: ['lint', 'linter'])]
function linter(): int
{
    io()->section('Running Linters...');

    $commands = [
        'bin/console lint:container',
        'bin/console lint:yaml config',
    ];

    return max(array_map(
        static fn (string $command): int => exit_code($command, context: context()->withWorkingDirectory('api')),
        $commands,
    ));
}

#[AsTask(description: 'Runs Schema validation', aliases: ['schema'])]
function schema(): int
{
    io()->section('Running Schema validation...');

    return exit_code('bin/console doctrine:schema:validate --skip-sync', context: context()->withWorkingDirectory('api'));
}
