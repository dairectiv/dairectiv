<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Workflow\ListWorkflows;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Workflow\WorkflowSearchCriteria;
use Dairectiv\Authoring\Domain\Repository\WorkflowRepository;
use Dairectiv\SharedKernel\Application\Query\QueryHandler;

final readonly class Handler implements QueryHandler
{
    public function __construct(private WorkflowRepository $workflowRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $state = null !== $input->state ? DirectiveState::tryFrom($input->state) : null;

        $criteria = new WorkflowSearchCriteria(
            search: $input->search,
            state: $state,
            sortBy: $input->sortBy,
            sortOrder: $input->sortOrder,
        );

        $offset = ($input->page - 1) * $input->limit;

        $items = $this->workflowRepository->searchByCriteria($criteria, $offset, $input->limit);
        $total = $this->workflowRepository->countByCriteria($criteria);

        return new Output($items, $total, $input->page, $input->limit);
    }
}
