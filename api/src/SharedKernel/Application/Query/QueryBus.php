<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Application\Query;

interface QueryBus
{
    public function fetch(Query $query): object;
}
