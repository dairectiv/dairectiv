<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Rule\Draft\Payload;

use Symfony\Component\Validator\Constraints;

final class ExamplePayload
{
    public function __construct(
        #[Constraints\When(
            expression: 'this.bad === null',
            constraints: [new Constraints\NotBlank()],
            otherwise: [new Constraints\NotBlank(allowNull: true)],
        )]
        public ?string $good = null,

        #[Constraints\When(
            expression: 'this.good === null',
            constraints: [new Constraints\NotBlank()],
            otherwise: [new Constraints\NotBlank(allowNull: true)],
        )]
        public ?string $bad = null,

        #[Constraints\NotBlank(allowNull: true)]
        public ?string $explanation = null,
    ) {
    }

    /**
     * @return array{good?: ?string, bad?: ?string, explanation?: ?string}
     */
    public function toState(): array
    {
        return [
            'good'        => $this->good,
            'bad'         => $this->bad,
            'explanation' => $this->explanation,
        ];
    }
}
