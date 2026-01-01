<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Response\Rule;

use Dairectiv\Authoring\Application\Rule\ListRules\Output;

final readonly class RulesCollectionResponse
{
    /**
     * @param list<RuleResponse> $items
     */
    private function __construct(
        public array $items,
        public PaginationResponse $pagination,
    ) {
    }

    public static function fromOutput(Output $output): self
    {
        $items = array_map(
            static fn ($rule) => RuleResponse::fromRule($rule),
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
