<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\UseCaseRule\InvalidWrongParameterName;

use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler // Error: line 9 - parameter must be named "input"
{
    public function __invoke(Input $command): void
    {
    }
}
