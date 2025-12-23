<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Application\Command;

interface CommandBus
{
    public function execute(Command $command): ?object;
}
