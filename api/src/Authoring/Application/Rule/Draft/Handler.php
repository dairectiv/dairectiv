<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\Draft;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\Authoring\Domain\Repository\RuleRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;
use function Symfony\Component\String\u;

final readonly class Handler implements CommandHandler
{
    public function __construct(
        private DirectiveRepository $directiveRepository,
        private RuleRepository $ruleRepository,
    ) {
    }

    public function __invoke(Input $input): Output
    {
        $id = DirectiveId::fromString(u($input->name)->kebab()->toString());

        if (null !== $this->directiveRepository->findDirectiveById($id)) {
            throw DirectiveAlreadyExistsException::fromId($id);
        }

        $rule = Rule::draft(
            $id,
            $input->name,
            $input->description,
        );

        $this->ruleRepository->save($rule);

        return new Output($rule);
    }
}
