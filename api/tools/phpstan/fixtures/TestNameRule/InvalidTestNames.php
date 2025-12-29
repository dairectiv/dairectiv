<?php

declare(strict_types=1);

namespace Dairectiv\PHPStan\Fixtures\TestNameRule;

use PHPUnit\Framework\TestCase;

final class InvalidTestNames extends TestCase
{
    public function testSomething(): void // Error: line 11
    {
    }

    public function testDoSomething(): void // Error: line 15
    {
    }

    public function testHandleEdgeCase(): void // Error: line 19
    {
    }

    public function testItShouldBeValid(): void // Valid
    {
    }
}
