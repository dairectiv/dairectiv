<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\DraftWorkflow;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\Authoring\Domain\Repository\WorkflowRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use function Symfony\Component\String\u;

final readonly class Handler implements CommandHandler
{
    public function __construct(
        private DirectiveRepository $directiveRepository,
        private WorkflowRepository $workflowRepository,
    ) {
    }

    public function __invoke(Input $input): Output
    {
        $id = DirectiveId::fromString(u($input->name)->kebab()->toString());

        if (null !== $this->directiveRepository->findDirectiveById($id)) {
            throw DirectiveAlreadyExistsException::fromId($id);
        }

        $workflow = Workflow::draft(
            $id,
            $input->name,
            $input->description,
        );

        $this->workflowRepository->save($workflow);

        return new Output($workflow);
    }
}
