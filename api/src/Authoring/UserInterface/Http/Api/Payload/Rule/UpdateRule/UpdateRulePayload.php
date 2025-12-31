<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Rule\UpdateRule;

use Symfony\Component\Validator\Constraints;

final readonly class UpdateRulePayload
{
    public function __construct(
        #[Constraints\Length(max: 255)]
        public ?string $name = null,
        #[Constraints\Length(max: 500)]
        public ?string $description = null,
        public ?string $content = null,
    ) {
    }
}
