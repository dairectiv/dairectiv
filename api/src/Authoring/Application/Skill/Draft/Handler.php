<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Application\Skill\Draft;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
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

    public function __invoke(Input $input): Output
    {
        $skill = Skill::draft(
            DirectiveId::fromString($input->id),
            DirectiveMetadata::create(
                DirectiveName::fromString($input->name),
                DirectiveDescription::fromString($input->description),
            ),
            SkillContent::fromString($input->content),
            SkillWorkflow::fromArray($input->workflow),
            $this->buildExamples($input->examples),
        );

        $this->directiveRepository->save($skill);

        return new Output($skill);
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
