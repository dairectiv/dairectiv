<?php

declare(strict_types=1);

namespace composer;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use function Castor\context;
use function Castor\exit_code;
use function Castor\io;

/**
 * Execute a composer command in the API container
 */
function composer(string $command): int
{
    return exit_code("composer $command", context: context()->withWorkingDirectory('api'));
}

#[AsTask(description: 'Updates dependencies', aliases: ['update'])]
function update(): int
{
    io()->section('Updating dependencies...');

    return composer('update');
}

#[AsTask(description: 'Installs dependencies', aliases: ['install'])]
function install(): int
{
    io()->section('Installing dependencies...');

    return composer('install');
}

#[AsTask(name:'require', description: 'Requires dependency', aliases: ['req', 'require'])]
function req(
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

#[AsTask(description: 'Removes dependency', aliases: ['remove'])]
function remove(string $name): int
{
    io()->section('Removing dependency...');

    return composer("remove $name");
}
