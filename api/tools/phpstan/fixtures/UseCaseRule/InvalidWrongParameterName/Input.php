<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\UseCaseRule\InvalidWrongParameterName;

use Dairectiv\SharedKernel\Application\Command\Command;

final readonly class Input implements Command
{
    public function __construct(
        public string $id,
    ) {
    }
}
