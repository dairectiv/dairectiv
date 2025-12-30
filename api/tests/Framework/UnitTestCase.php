<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Framework;

use Cake\Chronos\Chronos;
use Dairectiv\Tests\Framework\Assertions\AggregateRootAssertions;
use Dairectiv\Tests\Framework\Helpers\AuthoringHelpers;
use PHPUnit\Framework\TestCase;

abstract class UnitTestCase extends TestCase
{
    use AggregateRootAssertions {
        setUp as setUpAggregateRootAssertions;
        tearDown as tearDownAggregateRootAssertions;
    }
    use AuthoringHelpers;
    use ReflectionAssertions;

    protected function setUp(): void
    {
        parent::setUp();
        Chronos::setTestNow(Chronos::now());
        $this->setUpAggregateRootAssertions();
    }

    protected function tearDown(): void
    {
        $this->tearDownAggregateRootAssertions();
        parent::tearDown();
    }
}
