<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Skill\Workflow;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\ObjectValue;

final readonly class SkillTemplate implements ObjectValue
{
    private function __construct(
        public string $name,
        public string $content,
        public ?string $description,
    ) {
    }

    public static function create(string $name, string $content, ?string $description = null): self
    {
        Assert::notEmpty($name, 'Template name cannot be empty.');
        Assert::notEmpty($content, 'Template content cannot be empty.');

        return new self($name, $content, $description);
    }

    public static function fromArray(array $state): static
    {
        Assert::keyExists($state, 'name');
        Assert::string($state['name']);

        Assert::keyExists($state, 'content');
        Assert::string($state['content']);

        $description = $state['description'] ?? null;
        Assert::nullOrString($description);

        return new self($state['name'], $state['content'], $description);
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'content'     => $this->content,
            'description' => $this->description,
        ];
    }
}
