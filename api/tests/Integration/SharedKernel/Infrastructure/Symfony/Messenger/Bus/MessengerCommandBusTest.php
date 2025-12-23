<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\SharedKernel\Infrastructure\Symfony\Messenger\Bus;

use Dairectiv\Tests\Fixtures\Command;
use Dairectiv\Tests\Fixtures\Domain\FakeEvent;
use Dairectiv\Tests\Fixtures\ErrorCommand;
use Dairectiv\Tests\Framework\IntegrationTestCase;

final class MessengerCommandBusTest extends IntegrationTestCase
{
    public function testItShouldExecuteCommand(): void
    {
        $output = $this->execute(new Command\Input('foo'));
        self::assertEquals(new Command\Output('foo'), $output);
        self::assertDomainEventHasBeenDispatched(FakeEvent::class);
    }

    public function testItShouldThrowException(): void
    {
        $this->expectExceptionObject(new \RuntimeException('This command is not supported yet'));
        $this->execute(new ErrorCommand\Input('foo'));
    }
}
