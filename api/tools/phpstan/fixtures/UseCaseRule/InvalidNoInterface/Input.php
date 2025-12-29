<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\UseCaseRule\InvalidNoInterface;

final readonly class Input
{
    public function __construct(
        public string $id,
    ) {
    }
}
