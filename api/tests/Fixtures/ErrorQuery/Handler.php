<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Fixtures\ErrorQuery;

use Dairectiv\SharedKernel\Application\Query\QueryHandler;

final readonly class Handler implements QueryHandler
{
    public function __invoke(Input $input): never
    {
        throw new \RuntimeException('This query is not supported yet');
    }
}
