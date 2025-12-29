<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\UseCaseRule\ValidQueryHandler;

use Dairectiv\SharedKernel\Application\Query\QueryHandler;

final readonly class Handler implements QueryHandler
{
    public function __invoke(Input $input): Output
    {
        return new Output('result');
    }
}
