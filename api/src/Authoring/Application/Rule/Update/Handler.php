<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\Update;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
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
        Assert::true(
            null !== $input->name || null !== $input->description || null !== $input->content,
            'At least one field must be provided.',
        );

        $id = DirectiveId::fromString($input->id);
        $rule = $this->directiveRepository->getDirectiveById($id);

        \assert($rule instanceof Rule);

        if (null !== $input->name || null !== $input->description) {
            $rule->updateMetadata($input->name, $input->description);
        }

        if (null !== $input->content) {
            $rule->updateContent($input->content);
        }

        $this->directiveRepository->save($rule);
    }
}
