<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\UseCaseRule\InvalidNoInterface;

final readonly class Handler // Error: line 7 - must implement QueryHandler or CommandHandler
{
    public function __invoke(Input $input): void
    {
    }
}
