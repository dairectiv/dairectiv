<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\Update;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
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
        Assert::true(
            null !== $input->name || null !== $input->description || null !== $input->content,
            'At least one field must be provided.'
        );

        $id = DirectiveId::fromString($input->id);
        $skill = $this->directiveRepository->getDirectiveById($id);

        \assert($skill instanceof Skill);

        if (null !== $input->name || null !== $input->description) {
            $skill->updateMetadata($input->name, $input->description);
        }

        if (null !== $input->content) {
            $skill->updateContent($input->content);
        }

        $this->directiveRepository->save($skill);
    }
}
