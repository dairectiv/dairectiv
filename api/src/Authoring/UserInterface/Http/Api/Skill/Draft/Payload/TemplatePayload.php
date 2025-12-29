<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Symfony\Component\Validator\Constraints;

final class TemplatePayload
{
    public function __construct(
        #[Constraints\NotBlank]
        public string $name,

        #[Constraints\NotBlank]
        public string $content,

        #[Constraints\NotBlank(allowNull: true)]
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
