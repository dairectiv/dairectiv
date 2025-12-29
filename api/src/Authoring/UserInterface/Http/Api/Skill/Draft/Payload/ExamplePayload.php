<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Symfony\Component\Validator\Constraints;

final class ExamplePayload
{
    public function __construct(
        #[Constraints\NotBlank]
        public string $scenario,

        #[Constraints\NotBlank]
        public string $input,

        #[Constraints\NotBlank]
        public string $output,

        #[Constraints\NotBlank(allowNull: true)]
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
