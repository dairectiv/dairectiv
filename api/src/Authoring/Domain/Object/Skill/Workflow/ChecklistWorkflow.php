<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\SharedKernel\Domain\Object\Assert;

/**
 * Checklist workflow for task checklists.
 */
final readonly class ChecklistWorkflow extends SkillWorkflow
{
    /**
     * @param list<SkillStep> $items
     */
    private function __construct(public array $items)
    {
    }

    /**
     * @param list<SkillStep> $items
     */
    public static function create(array $items): self
    {
        Assert::notEmpty($items, 'Checklist workflow must have at least one item.');

        return new self($items);
    }

    public function getType(): WorkflowType
    {
        return WorkflowType::Checklist;
    }

    public function itemCount(): int
    {
        return \count($this->items);
    }

    public static function fromState(array $state): static
    {
        Assert::keyExists($state, 'items');
        Assert::isArray($state['items']);
        Assert::allIsArray($state['items']);

        $items = array_values(array_map(SkillStep::fromArray(...), $state['items']));

        return new self($items);
    }

    public function toArray(): array
    {
        return [
            'type'  => $this->getType()->value,
            'items' => array_map(static fn (SkillStep $item): array => $item->toArray(), $this->items),
        ];
    }
}
