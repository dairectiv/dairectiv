<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Fixtures\Command;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    public function __construct(public string $foo)
    {
    }
}
