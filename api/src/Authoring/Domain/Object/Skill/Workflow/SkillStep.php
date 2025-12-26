<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\ObjectValue;

final readonly class SkillStep implements ObjectValue
{
    private function __construct(
        public int $order,
        public string $title,
        public string $content,
        public StepType $type,
        public ?string $condition,
    ) {
    }

    public static function create(
        int $order,
        string $title,
        string $content,
        StepType $type,
        ?string $condition = null,
    ): self {
        Assert::positiveInteger($order, 'Step order must be a positive integer.');
        Assert::notEmpty($title, 'Step title cannot be empty.');
        Assert::notEmpty($content, 'Step content cannot be empty.');

        return new self($order, $title, $content, $type, $condition);
    }

    public static function action(int $order, string $title, string $content, ?string $condition = null): self
    {
        return self::create($order, $title, $content, StepType::Action, $condition);
    }

    public static function decision(int $order, string $title, string $content, ?string $condition = null): self
    {
        return self::create($order, $title, $content, StepType::Decision, $condition);
    }

    public static function template(int $order, string $title, string $content, ?string $condition = null): self
    {
        return self::create($order, $title, $content, StepType::Template, $condition);
    }

    public static function validation(int $order, string $title, string $content, ?string $condition = null): self
    {
        return self::create($order, $title, $content, StepType::Validation, $condition);
    }

    public function isConditional(): bool
    {
        return null !== $this->condition;
    }

    public static function fromArray(array $state): static
    {
        Assert::keyExists($state, 'order');
        Assert::integer($state['order']);

        Assert::keyExists($state, 'title');
        Assert::string($state['title']);

        Assert::keyExists($state, 'content');
        Assert::string($state['content']);

        Assert::keyExists($state, 'type');
        Assert::string($state['type']);

        $condition = $state['condition'] ?? null;
        Assert::nullOrString($condition);

        return new self(
            $state['order'],
            $state['title'],
            $state['content'],
            StepType::from($state['type']),
            $condition,
        );
    }

    public function toArray(): array
    {
        return [
            'order'     => $this->order,
            'title'     => $this->title,
            'content'   => $this->content,
            'type'      => $this->type->value,
            'condition' => $this->condition,
        ];
    }
}
