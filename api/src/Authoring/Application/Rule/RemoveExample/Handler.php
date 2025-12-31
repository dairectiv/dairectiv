<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\RemoveExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Example\Example;
use Dairectiv\Authoring\Domain\Object\Rule\Example\ExampleId;
use Dairectiv\Authoring\Domain\Repository\RuleRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use Dairectiv\SharedKernel\Domain\Object\Assert;

final readonly class Handler implements CommandHandler
{
    public function __construct(private RuleRepository $ruleRepository)
    {
    }

    public function __invoke(Input $input): void
    {
        $ruleId = DirectiveId::fromString($input->ruleId);
        $rule = $this->ruleRepository->getRuleById($ruleId);

        $exampleId = ExampleId::fromString($input->exampleId);
        $example = $rule->examples->findFirst(
            static fn (int $key, Example $e) => $e->id->equals($exampleId),
        );

        Assert::notNull($example, \sprintf('Example with ID "%s" not found.', $input->exampleId));

        $rule->removeExample($example);
    }
}
