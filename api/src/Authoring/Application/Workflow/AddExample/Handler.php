<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\AddExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\Example;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $id = DirectiveId::fromString($input->workflowId);
        $workflow = $this->directiveRepository->getDirectiveById($id);

        \assert($workflow instanceof Workflow);

        $example = Example::create(
            $workflow,
            $input->scenario,
            $input->input,
            $input->output,
            $input->explanation,
        );

        $this->directiveRepository->save($workflow);

        return new Output($example);
    }
}
