<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\ListRules;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveState;
use Dairectiv\Authoring\Domain\Object\Rule\RuleSearchCriteria;
use Dairectiv\Authoring\Domain\Repository\RuleRepository;
use Dairectiv\SharedKernel\Application\Query\QueryHandler;

final readonly class Handler implements QueryHandler
{
    public function __construct(private RuleRepository $ruleRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $state = null !== $input->state ? DirectiveState::tryFrom($input->state) : null;

        $criteria = new RuleSearchCriteria(
            search: $input->search,
            state: $state,
            sortBy: $input->sortBy,
            sortOrder: $input->sortOrder,
        );

        $offset = ($input->page - 1) * $input->limit;

        $items = $this->ruleRepository->searchByCriteria($criteria, $offset, $input->limit);
        $total = $this->ruleRepository->countByCriteria($criteria);

        return new Output($items, $total, $input->page, $input->limit);
    }
}
