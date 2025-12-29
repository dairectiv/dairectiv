<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\UseCaseRule\ValidQueryHandler;

final readonly class Output
{
    public function __construct(
        public string $result,
    ) {
    }
}
