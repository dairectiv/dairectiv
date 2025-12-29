<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\SharedKernel\Infrastructure\Symfony\Messenger\Bus;

use Dairectiv\Tests\Fixtures\ErrorQuery;
use Dairectiv\Tests\Fixtures\Query;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('shared-kernel')]
final class MessengerQueryBusTest extends IntegrationTestCase
{
    public function testItShouldFetchQuery(): void
    {
        $output = $this->fetch(new Query\Input('foo'));
        self::assertEquals(new Query\Output('foo'), $output);
    }

    public function testItShouldThrowException(): void
    {
        $this->expectExceptionObject(new \RuntimeException('This query is not supported yet'));
        $this->fetch(new ErrorQuery\Input('foo'));
    }
}
