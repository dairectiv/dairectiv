<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Fixtures\Command;

use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use Dairectiv\Tests\Fixtures\Domain\FakeAggregate;

final readonly class Handler implements CommandHandler
{
    public function __invoke(Input $input): Output
    {
        $aggregate = new FakeAggregate($input->foo);

        return new Output($aggregate->foo);
    }
}
