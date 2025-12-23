<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\SharedKernel\Infrastructure\Symfony\Messenger\Bus;

use Dairectiv\Tests\Fixtures\Query;
use Dairectiv\Tests\Framework\IntegrationTestCase;

final class MessengerQueryBusTest extends IntegrationTestCase
{
    public function testItShouldFetchQuery(): void
    {
        $output = $this->fetch(new Query\Input('foo'));
        self::assertEquals(new Query\Output('foo'), $output);
    }
}
