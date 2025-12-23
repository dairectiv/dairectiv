<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Bus;

use Dairectiv\SharedKernel\Application\Command\Command;
use Dairectiv\SharedKernel\Application\Command\CommandBus;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerCommandBus implements CommandBus
{
    use HandleTrait {
        handle as handleMessage;
    }

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->messageBus = $commandBus;
    }

    public function execute(Command $command): ?object
    {
        try {
            /** @var ?object $output */
            $output = $this->handleMessage($command);

            return $output;
        } catch (HandlerFailedException $e) {
            $nested = $e->getWrappedExceptions();

            if (\count($nested) > 1) {
                throw new \LogicException('Bus cannot manage more than one nested exception from Symfony Messenger', 0, $e); // @codeCoverageIgnore
            }

            $current = current($nested);

            if ($current instanceof \Throwable) {
                throw $current;
            }

            throw $e; // @codeCoverageIgnore
        }
    }
}
