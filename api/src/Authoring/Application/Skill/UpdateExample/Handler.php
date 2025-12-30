<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\UpdateExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Skill\Example\ExampleId;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
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
        $skillId = DirectiveId::fromString($input->skillId);
        $skill = $this->directiveRepository->getDirectiveById($skillId);

        \assert($skill instanceof Skill);

        $exampleId = ExampleId::fromString($input->exampleId);
        $example = $skill->examples->filter(
            static fn ($e) => $e->id->equals($exampleId),
        )->first();

        Assert::notFalse($example, \sprintf('Example with ID "%s" not found.', $input->exampleId));

        $example->update(
            $input->scenario,
            $input->input,
            $input->output,
            $input->explanation,
        );

        $this->directiveRepository->save($skill);
    }
}
