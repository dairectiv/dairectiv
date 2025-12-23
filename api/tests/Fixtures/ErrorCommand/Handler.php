<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Fixtures\ErrorCommand;

use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler
{
    public function __invoke(Input $input): void
    {
        throw new \RuntimeException('This command is not supported yet');
    }
}
