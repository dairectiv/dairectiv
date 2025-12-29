<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

final class ExamplePayload
{
    public function __construct(
        public string $scenario,

        public string $input,

        public string $output,

        public ?string $explanation,
    ) {
    }

    /**
     * @return array{scenario: string, input: string, output: string, explanation?: ?string}
     */
    public function toState(): array
    {
        return [
            'scenario'    => $this->scenario,
            'input'       => $this->input,
            'output'      => $this->output,
            'explanation' => $this->explanation,
        ];
    }
}
