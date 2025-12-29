<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

final class StepPayload
{
    public function __construct(
        public int $order,

        public string $title,

        public string $content,

        public string $type,

        public ?string $condition = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toState(): array
    {
        return [
            'order'     => $this->order,
            'title'     => $this->title,
            'content'   => $this->content,
            'type'      => $this->type,
            'condition' => $this->condition,
        ];
    }
}
