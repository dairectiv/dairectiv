<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\SharedKernel\Domain\Object\Assert;

/**
 * Hybrid workflow combining sequential steps with templates.
 */
final readonly class HybridWorkflow extends SkillWorkflow
{
    /**
     * @param list<SkillStep> $steps
     * @param list<SkillTemplate> $templates
     */
    private function __construct(
        public array $steps,
        public array $templates,
    ) {
    }

    /**
     * @param list<SkillStep> $steps
     * @param list<SkillTemplate> $templates
     */
    public static function create(array $steps, array $templates): self
    {
        Assert::true(
            [] !== $steps || [] !== $templates,
            'Hybrid workflow must have at least one step or template.',
        );

        return new self($steps, $templates);
    }

    public function getType(): WorkflowType
    {
        return WorkflowType::Hybrid;
    }

    public function stepCount(): int
    {
        return \count($this->steps);
    }

    public function templateCount(): int
    {
        return \count($this->templates);
    }

    public static function fromState(array $state): static
    {
        $steps = [];
        if (isset($state['steps'])) {
            Assert::isArray($state['steps']);
            Assert::allIsArray($state['steps']);
            $steps = array_values(array_map(SkillStep::fromArray(...), $state['steps']));
        }

        $templates = [];
        if (isset($state['templates'])) {
            Assert::isArray($state['templates']);
            Assert::allIsArray($state['templates']);
            $templates = array_values(array_map(SkillTemplate::fromArray(...), $state['templates']));
        }

        return new self($steps, $templates);
    }

    public function toArray(): array
    {
        return [
            'type'      => $this->getType()->value,
            'steps'     => array_map(static fn (SkillStep $step): array => $step->toArray(), $this->steps),
            'templates' => array_map(static fn (SkillTemplate $template): array => $template->toArray(), $this->templates),
        ];
    }
}
