<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\RemoveExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Rule\Example\ExampleId;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use Dairectiv\SharedKernel\Domain\Object\Assert;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): void
    {
        $ruleId = DirectiveId::fromString($input->ruleId);
        $rule = $this->directiveRepository->getDirectiveById($ruleId);

        \assert($rule instanceof Rule);

        $exampleId = ExampleId::fromString($input->exampleId);
        $example = $rule->examples->filter(
            static fn ($e) => $e->id->equals($exampleId)
        )->first();

        Assert::notFalse($example, \sprintf('Example with ID "%s" not found.', $input->exampleId));

        $rule->removeExample($example);

        $this->directiveRepository->save($rule);
    }
}
