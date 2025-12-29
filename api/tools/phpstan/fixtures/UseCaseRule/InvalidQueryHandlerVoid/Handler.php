<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\UseCaseRule\InvalidQueryHandlerVoid;

use Dairectiv\SharedKernel\Application\Query\QueryHandler;

final readonly class Handler implements QueryHandler // Error: line 9 - QueryHandler must return Output
{
    public function __invoke(Input $input): void
    {
    }
}
