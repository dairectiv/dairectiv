<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\Update;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\SkillContent;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExample;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExamples;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\SharedKernel\Application\Command\CommandHandler;

final readonly class Handler implements CommandHandler
{
    public function __construct(private DirectiveRepository $directiveRepository)
    {
    }

    public function __invoke(Input $input): void
    {
        /** @var Skill $skill */
        $skill = $this->directiveRepository->getDirectiveById(
            DirectiveId::fromString($input->id),
        );

        if (null !== $input->name || null !== $input->description) {
            $skill->updateMetadata(
                null !== $input->name ? DirectiveName::fromString($input->name) : null,
                null !== $input->description ? DirectiveDescription::fromString($input->description) : null,
            );
        }

        if (null !== $input->content || null !== $input->workflow || null !== $input->examples) {
            $skill->updateContent(
                null !== $input->content ? SkillContent::fromString($input->content) : null,
                null !== $input->workflow ? SkillWorkflow::fromArray($input->workflow) : null,
                null !== $input->examples ? $this->buildExamples($input->examples) : null,
            );
        }
    }

    /**
     * @param list<array{scenario: string, input: string, output: string, explanation?: ?string}> $examples
     */
    private function buildExamples(array $examples): SkillExamples
    {
        if ([] === $examples) {
            return SkillExamples::empty();
        }

        return SkillExamples::fromList(
            array_map(
                static fn (array $example): SkillExample => SkillExample::create(
                    $example['scenario'],
                    $example['input'],
                    $example['output'],
                    $example['explanation'] ?? null,
                ),
                $examples,
            ),
        );
    }
}
