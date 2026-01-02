<?php

declare(strict_types=1);

namespace infra;

use Castor\Attribute\AsOption;
use Castor\Attribute\AsTask;
use Castor\Context;
use Symfony\Component\Process\Exception\ExceptionInterface;
use Symfony\Component\Process\Process;
use function Castor\io;
use function Castor\run;

#[AsTask(description: 'Builds the infrastructure', aliases: ['build'])]
function build(
    #[AsOption(description: 'The service to build (default: all services)', autocomplete: 'infra\get_service_names')]
    ?string $service = null,
): void {
    io()->title('Building infrastructure');

    $command = ['build'];

    if ($service) {
        $command[] = $service;
    }

    docker_compose($command);
}

#[AsTask(description: 'Builds and starts the infrastructure', aliases: ['up'])]
function up(
    #[AsOption(description: 'The service to start (default: all services)', autocomplete: 'infra\get_service_names')]
    ?string $service = null,
): void {
    if (!$service) {
        io()->title('Starting infrastructure');
    }

    $command = ['up', '--detach', '--wait', '--no-build'];

    if ($service) {
        $command[] = $service;
    }

    try {
        docker_compose($command);
    } catch (ExceptionInterface $e) {
        io()->error('An error occurred while starting the infrastructure.');
        io()->note('Did you forget to run "castor infra:build"?');
        io()->note('Or you forget to login to the registry?');

        throw $e;
    }
}

#[AsTask(description: 'Stops the infrastructure', aliases: ['down'])]
function down(
    #[AsOption(description: 'The service to stop (default: all services)', autocomplete: 'infra\get_service_names')]
    ?string $service = null,
): void {
    if (!$service) {
        io()->title('Stopping infrastructure');
    }

    $command = ['stop'];

    if ($service) {
        $command[] = $service;
    }

    docker_compose($command);
}

#[AsTask(description: 'Displays infrastructure logs', aliases: ['logs'])]
function logs(?string $service = null): void
{
    $command = ['logs', '-f', '--tail', '150'];

    if ($service) {
        $command[] = $service;
    }

    docker_compose($command, context()->withTty());
}

#[AsTask(description: 'Cleans the infrastructure (remove container, volume, networks)', aliases: ['destroy'])]
function destroy(
    #[AsOption(shortcut: 'f', description: 'Force the destruction without confirmation')]
    bool $force = false,
): void {
    io()->title('Destroying infrastructure');

    if (!$force) {
        io()->warning('This will permanently remove all containers, volumes, networks... created for this project.');
        io()->note('You can use the --force option to avoid this confirmation.');
        if (!io()->confirm('Are you sure?', false)) {
            io()->comment('Aborted.');

            return;
        }
    }

    docker_compose(['down', '--remove-orphans', '--volumes', '--rmi=local']);
}

#[AsTask(description: 'Lists containers status', aliases: ['ps'])]
function ps(bool $ports = false): void
{
    $command = [
        'ps',
        '--format', 'table {{.Name}}\t{{.Image}}\t{{.Status}}\t{{.RunningFor}}\t{{.Command}}',
        '--no-trunc',
    ];

    if ($ports) {
        $command[2] .= '\t{{.Ports}}';
    }

    docker_compose($command, context());

    if (!$ports) {
        io()->comment('You can use the "--ports" option to display ports.');
    }
}

/**
 * @param string[] $subCommand
 */
function docker_compose(array $subCommand, ?Context $c = null): Process
{
    $c ??= context();

    $command = [
        'docker',
        'compose',
        '-f',
        $c['compose_file'],
    ];

    $command = array_merge($command, $subCommand);

    return run($command, context: $c);
}

/**
 * Run a command in a service container
 *
 * @param string $runCommand The command to run
 * @param Context|null $c The context
 * @param string $service The service name (default: api)
 * @return Process
 */
function docker_compose_run(
    string $runCommand,
    ?Context $c = null,
    string $service = 'api',
): Process {
    $c ??= context();

    $command = [
        'run',
        '--rm',
    ];

    $command[] = $service;
    $command[] = '/bin/sh'; // Alpine uses /bin/sh instead of /bin/bash
    $command[] = '-c';
    $command[] = "{$runCommand}";

    return docker_compose($command, c: $c);
}

/**
 * Run a command and return its exit code
 *
 * @param string $runCommand The command to run
 * @param Context|null $c The context
 * @param string $service The service name (default: api)
 * @return int The exit code
 */
function docker_exit_code(
    string $runCommand,
    ?Context $c = null,
    string $service = 'api',
): int {
    $c = ($c ?? context())->withAllowFailure();

    $process = docker_compose_run(
        runCommand: $runCommand,
        c: $c,
        service: $service,
    );

    return $process->getExitCode() ?? 0;
}

/**
 * @return array{
 *     name: string,
 *     networks: array<string, array{name: string, ipam: array<string, mixed>}>,
 *     services: array<string, array{
 *          container_name: string,
 *          environment: array<string, string>,
 *          command: ?string,
 *          entrypoint: ?string,
 *          build?: array{
 *              context: string,
 *              dockerfile: string,
 *              args: array<string, string>,
 *              target: string
 *          },
 *          image?: string,
 *          ports?: array<array{mode: string, target: int, published: string, protocol: string}>,
 *          networks?: array<string, mixed>,
 *          volumes?: array<array{type: string, type: string, target: string, bind?: array{create_host_path: bool}, volume?: array<array-key, mixed>}>
 *     }>,
 *     volumes: array<string, array{name: string}>
 * }
 */
function get_config(): array
{
    $config = docker_compose(
        ['config', '--format', 'json'],
        context()->withQuiet(),
    );

    return json_decode($config->getOutput(), true);
}


/**
 * @return string[]
 */
function get_service_names(): array
{
    $config = get_config();

    return array_keys($config['services']);
}
