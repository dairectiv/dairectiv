<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\TestNameRule;

use PHPUnit\Framework\TestCase;

final class ValidTestNames extends TestCase
{
    public function testItShouldDoSomething(): void
    {
    }

    public function testItShouldHandleEdgeCase(): void
    {
    }

    public function testItShouldReturnCorrectValue(): void
    {
    }

    // Non-test methods are ignored
    public function setUp(): void
    {
    }

    public function helperMethod(): void
    {
    }
}
