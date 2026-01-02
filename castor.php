<?php

declare(strict_types=1);

use Castor\Attribute\AsContext;
use Castor\Attribute\AsTask;

use Castor\Context;
use Symfony\Component\Process\Process;
use function Castor\import;
use function Castor\io;

import(__DIR__ . '/.castor');

#[AsContext]
function context(): Context
{
    return new Context(
        data: [
            'compose_file' => __DIR__ . '/compose.yaml',
        ],
        pty: Process::isPtySupported(),
    );
}

#[AsTask(description: 'Resets and starts the full infrastructure with dependencies')]
function start(): void
{
    io()->title('Starting fresh environment');

    infra\destroy(force: true);
    infra\build();
    infra\up();
    api\install();
    db\reset(allEnvs: true);

    io()->success('Environment is ready!');
}
