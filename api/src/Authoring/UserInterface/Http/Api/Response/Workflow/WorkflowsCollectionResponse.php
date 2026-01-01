<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow;

use Dairectiv\Authoring\Application\Workflow\ListWorkflows\Output;

final readonly class WorkflowsCollectionResponse
{
    /**
     * @param list<WorkflowResponse> $items
     */
    private function __construct(
        public array $items,
        public PaginationResponse $pagination,
    ) {
    }

    public static function fromOutput(Output $output): self
    {
        $items = array_map(
            static fn ($workflow) => WorkflowResponse::fromWorkflow($workflow),
            $output->items,
        );

        return new self(
            $items,
            new PaginationResponse(
                page: $output->page,
                limit: $output->limit,
                total: $output->total,
                totalPages: $output->totalPages(),
                hasNextPage: $output->hasNextPage(),
                hasPreviousPage: $output->hasPreviousPage(),
            ),
        );
    }
}
