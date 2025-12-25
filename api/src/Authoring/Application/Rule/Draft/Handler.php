<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Rule\Draft;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
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

    public function __invoke(Input $input): Output
    {
        $rule = Rule::draft(
            DirectiveId::fromString($input->id),
            DirectiveMetadata::create(
                DirectiveName::fromString($input->name),
                DirectiveDescription::fromString($input->description),
            ),
            RuleContent::fromString($input->content),
            $this->buildExamples($input->examples),
        );

        $this->directiveRepository->save($rule);

        return new Output($rule);
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
