<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\StepType;
use Symfony\Component\Validator\Constraints;

final class StepPayload
{
    public function __construct(
        #[Constraints\NotBlank]
        #[Constraints\Positive]
        public int $order,

        #[Constraints\NotBlank]
        public string $title,

        #[Constraints\NotBlank]
        public string $content,

        #[Constraints\Choice(callback: [StepType::class, 'values'])]
        public string $type,

        #[Constraints\NotBlank(allowNull: true)]
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
