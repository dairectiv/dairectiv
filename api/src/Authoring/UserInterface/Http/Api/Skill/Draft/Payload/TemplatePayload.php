<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

final class TemplatePayload
{
    public function __construct(
        public string $name,

        public string $content,

        public ?string $description = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toState(): array
    {
        return [
            'name'        => $this->name,
            'content'     => $this->content,
            'description' => $this->description,
        ];
    }
}
