<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\UpdateExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
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
        Assert::true(
            null !== $input->good || null !== $input->bad || null !== $input->explanation,
            'At least one field must be provided.',
        );

        $ruleId = DirectiveId::fromString($input->ruleId);
        $rule = $this->ruleRepository->getRuleById($ruleId);

        $exampleId = ExampleId::fromString($input->exampleId);
        $example = $rule->examples->filter(
            static fn ($e) => $e->id->equals($exampleId),
        )->first();

        Assert::notFalse($example, \sprintf('Example with ID "%s" not found.', $input->exampleId));

        $example->update(
            $input->good,
            $input->bad,
            $input->explanation,
        );
    }
}
