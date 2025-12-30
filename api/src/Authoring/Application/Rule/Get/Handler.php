<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\Get;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Repository\RuleRepository;
use Dairectiv\SharedKernel\Application\Query\QueryHandler;

final readonly class Handler implements QueryHandler
{
    public function __construct(private RuleRepository $ruleRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $id = DirectiveId::fromString($input->id);
        $rule = $this->ruleRepository->getRuleById($id);

        return new Output($rule);
    }
}
