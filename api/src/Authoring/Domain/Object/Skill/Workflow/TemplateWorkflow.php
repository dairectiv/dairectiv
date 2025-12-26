<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\SharedKernel\Domain\Object\Assert;

/**
 * Template workflow for patterns/templates to follow (like aggregate-root skill).
 */
final readonly class TemplateWorkflow extends SkillWorkflow
{
    /**
     * @param list<SkillTemplate> $templates
     */
    private function __construct(public array $templates)
    {
    }

    /**
     * @param list<SkillTemplate> $templates
     */
    public static function create(array $templates): self
    {
        Assert::notEmpty($templates, 'Template workflow must have at least one template.');

        return new self($templates);
    }

    public function getType(): WorkflowType
    {
        return WorkflowType::Template;
    }

    public function templateCount(): int
    {
        return \count($this->templates);
    }

    public static function fromState(array $state): static
    {
        Assert::keyExists($state, 'templates');
        Assert::isArray($state['templates']);
        Assert::allIsArray($state['templates']);

        $templates = array_values(array_map(SkillTemplate::fromArray(...), $state['templates']));

        return new self($templates);
    }

    public function toArray(): array
    {
        return [
            'type'      => $this->getType()->value,
            'templates' => array_map(static fn (SkillTemplate $template): array => $template->toArray(), $this->templates),
        ];
    }
}
