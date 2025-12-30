<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\RemoveExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\ExampleId;
use Dairectiv\Authoring\Domain\Repository\WorkflowRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use Dairectiv\SharedKernel\Domain\Object\Assert;

final readonly class Handler implements CommandHandler
{
    public function __construct(private WorkflowRepository $workflowRepository)
    {
    }

    public function __invoke(Input $input): void
    {
        $workflowId = DirectiveId::fromString($input->workflowId);
        $workflow = $this->workflowRepository->getWorkflowById($workflowId);

        $exampleId = ExampleId::fromString($input->exampleId);
        $example = $workflow->examples->filter(
            static fn ($e) => $e->id->equals($exampleId),
        )->first();

        Assert::notFalse($example, \sprintf('Example with ID "%s" not found.', $input->exampleId));

        $workflow->removeExample($example);
    }
}
