<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Payload\Rule\UpdateRuleExample;

final readonly class UpdateRuleExamplePayload
{
    public function __construct(
        public ?string $good = null,
        public ?string $bad = null,
        public ?string $explanation = null,
    ) {
    }
}
