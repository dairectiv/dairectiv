<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\Draft;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;

use function Symfony\Component\String\u;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): Output
    {
        $id = DirectiveId::fromString(u($input->name)->kebab()->toString());

        if (null !== $this->directiveRepository->findDirectiveById($id)) {
            throw DirectiveAlreadyExistsException::fromId($id);
        }

        $skill = Skill::draft(
            $id,
            $input->name,
            $input->description,
        );

        $this->directiveRepository->save($skill);

        return new Output($skill);
    }
}
