<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\Update;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Rule\Rule;
use Dairectiv\Authoring\Domain\Object\Rule\RuleContent;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExample;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExamples;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): void
    {
        /** @var Rule $rule */
        $rule = $this->directiveRepository->getDirectiveById(
            DirectiveId::fromString($input->id),
        );

        if (null !== $input->name || null !== $input->description) {
            $rule->updateMetadata(
                null !== $input->name ? DirectiveName::fromString($input->name) : null,
                null !== $input->description ? DirectiveDescription::fromString($input->description) : null,
            );
        }

        if (null !== $input->content || null !== $input->examples) {
            $rule->updateContent(
                null !== $input->content ? RuleContent::fromString($input->content) : null,
                null !== $input->examples ? $this->buildExamples($input->examples) : null,
            );
        }
    }

    /**
     * @param list<array{good?: ?string, bad?: ?string, explanation?: ?string}> $examples
     */
    private function buildExamples(array $examples): RuleExamples
    {
        if ([] === $examples) {
            return RuleExamples::empty();
        }

        return RuleExamples::fromList(
            array_map(
                static fn (array $example): RuleExample => RuleExample::create(
                    $example['good'] ?? null,
                    $example['bad'] ?? null,
                    $example['explanation'] ?? null,
                ),
                $examples,
            ),
        );
    }
}
