<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Symfony\Messenger\Bus;

use Dairectiv\SharedKernel\Application\Query\Query;
use Dairectiv\SharedKernel\Application\Query\QueryBus;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

final class MessengerQueryBus implements QueryBus
{
    use HandleTrait {
        handle as handleMessage;
    }

    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    public function fetch(Query $query): object
    {
        try {
            $output = $this->handleMessage($query);

            Assert::notNull($output, 'Query bus must return a value when handling a query.');
            Assert::object($output, 'Query bus must return an object.');

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
