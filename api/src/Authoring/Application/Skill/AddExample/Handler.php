<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\AddExample;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Skill\Example\Example;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $id = DirectiveId::fromString($input->skillId);
        $skill = $this->directiveRepository->getDirectiveById($id);

        \assert($skill instanceof Skill);

        $example = Example::create(
            $skill,
            $input->scenario,
            $input->input,
            $input->output,
            $input->explanation,
        );

        $this->directiveRepository->save($skill);

        return new Output($example);
    }
}
