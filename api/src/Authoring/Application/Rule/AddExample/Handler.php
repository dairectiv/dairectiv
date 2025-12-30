<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\AddExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Repository\RuleRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler
{
    public function __construct(private RuleRepository $ruleRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $ruleId = DirectiveId::fromString($input->ruleId);
        $rule = $this->ruleRepository->getRuleById($ruleId);

        $example = Example::create(
            $rule,
            $input->good,
            $input->bad,
            $input->explanation,
        );

        return new Output($example);
    }
}
