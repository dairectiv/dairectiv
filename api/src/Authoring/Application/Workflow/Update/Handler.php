<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\Update;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use Dairectiv\SharedKernel\Domain\Object\Assert;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): void
    {
        Assert::true(
            null !== $input->name || null !== $input->description || null !== $input->content,
            'At least one field must be provided.',
        );

        $id = DirectiveId::fromString($input->id);
        $workflow = $this->directiveRepository->getDirectiveById($id);

        \assert($workflow instanceof Workflow);

        if (null !== $input->name || null !== $input->description) {
            $workflow->updateMetadata($input->name, $input->description);
        }

        if (null !== $input->content) {
            $workflow->updateContent($input->content);
        }

        $this->directiveRepository->save($workflow);
    }
}
