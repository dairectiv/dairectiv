<?php

declare(strict_types=1);

namespace database;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\exit_code;
use function Castor\io;
use function docker\docker_compose;

#[AsTask(description: 'Resets database')]
function reset(
    #[AsOption(shortcut: 't', description: 'Use the test environment')]
    bool $test = false,
    #[AsOption(shortcut: 'f', description: 'Without fixtures')]
    bool $withoutFixtures = false,
    #[AsOption(shortcut: 'a', description: 'All environments')]
    bool $allEnvs = false,
): int {
    if ($allEnvs) {
        return max(
            reset(false, $withoutFixtures),
            reset(true, $withoutFixtures),
        );
    }

    docker_compose(['restart', 'postgres']);

    drop($test);

    io()->newLine(2);

    create($test);

    io()->newLine(2);

    migrate($test);

    if ($withoutFixtures) {
        return 0;
    }

    fixtures($test);

    return 0;
}

#[AsTask(description: 'Drops database')]
function drop(
    #[AsOption(shortcut: 't', description: 'Use the test environment')]
    bool $test = false,
): int {
    io()->section('Dropping database...');

    $command = 'bin/console doctrine:database:drop --force --if-exists';

    if ($test) {
        $command .= ' --env test';
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Creates database')]
function create(
    #[AsOption(shortcut: 't', description: 'Use the test environment')]
    bool $test = false,
): int {
    io()->section('Creating database...');

    $command = 'bin/console doctrine:database:create --if-not-exists';

    if ($test) {
        $command .= ' --env test';
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Migrates database')]
function migrate(
    #[AsOption(shortcut: 't', description: 'Use the test environment')]
    bool $test = false,
): int {
    io()->section('Migrating database...');

    $command = 'bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration';

    if ($test) {
        $command .= ' --env test';
    }

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Loads fixtures')]
function fixtures(
    #[AsOption(shortcut: 't', description: 'Use the test environment')]
    bool $test = false,
): int {
    io()->section('Load fixtures...');

    $command = 'bin/console foundry:load-fixtures --no-interaction -vv --no-debug';

    $command .= $test ? ' --env test' : ' --env dev';

    return exit_code($command, context: context()->withWorkingDirectory('api'));
}

#[AsTask(name: 'diff', description: 'Generate database\'s migration')]
function diff(
    #[AsOption(shortcut: 'r', description: 'Reset all environments')]
    bool $reset = false,
): int {
    io()->section('Generating database\'s migration...');

    if (0 !== exit_code('bin/console make:migration', context: context()->withWorkingDirectory('api'))) {
        return 1;
    }

    if ($reset) {
        return reset(allEnvs: true);
    }

    return 0;
}
