<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Directive\Archive;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): void
    {
        $directive = $this->directiveRepository->getDirectiveById(
            DirectiveId::fromString($input->id),
        );

        $directive->archive();
    }
}
