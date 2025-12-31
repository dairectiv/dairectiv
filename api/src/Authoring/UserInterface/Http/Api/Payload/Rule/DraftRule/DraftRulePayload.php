<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Rule\DraftRule;

use Symfony\Component\Validator\Constraints;

final readonly class DraftRulePayload
{
    public function __construct(
        #[Constraints\NotBlank]
        #[Constraints\Length(max: 255)]
        public string $name,
        #[Constraints\NotBlank]
        #[Constraints\Length(max: 500)]
        public string $description,
    ) {
    }
}
