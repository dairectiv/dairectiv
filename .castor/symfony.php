<?php

declare(strict_types=1);

namespace symfony;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;

use function Castor\context;
use function Castor\exit_code;
use function Castor\io;

#[AsTask(name: 'cache:clear', description: 'Clears the cache', aliases: ['cc'])]
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
