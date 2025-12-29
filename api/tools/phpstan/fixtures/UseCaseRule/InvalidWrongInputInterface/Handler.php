<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\UseCaseRule\InvalidWrongInputInterface;

use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler // Error: line 9 - Input must implement Command
{
    public function __invoke(Input $input): void
    {
    }
}
