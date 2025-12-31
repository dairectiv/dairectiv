<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Rule\AddRuleExample;

use Symfony\Component\Validator\Constraints;

final readonly class AddRuleExamplePayload
{
    public function __construct(
        #[Constraints\NotBlank]
        public string $good,
        #[Constraints\NotBlank]
        public string $bad,
        public ?string $explanation = null,
    ) {
    }
}
