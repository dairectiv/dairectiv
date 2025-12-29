<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\UseCaseRule\InvalidWrongInputInterface;

use Dairectiv\SharedKernel\Application\Query\Query;

// Input implements Query but Handler is CommandHandler
final readonly class Input implements Query
{
    public function __construct(
        public string $id,
    ) {
    }
}
