<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\AddExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $ruleId = DirectiveId::fromString($input->ruleId);
        $rule = $this->directiveRepository->getDirectiveById($ruleId);

        \assert($rule instanceof Rule);

        $example = Example::create(
            $rule,
            $input->good,
            $input->bad,
            $input->explanation,
        );

        $this->directiveRepository->save($rule);

        return new Output($example);
    }
}
