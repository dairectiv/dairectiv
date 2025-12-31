<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Object\Directive;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\StringValue;
use Symfony\Component\Uid\Uuid;
use function Safe\preg_match;
use function Symfony\Component\String\u;

final readonly class DirectiveId extends StringValue
{
    private const string UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    public static function validate(string $value): void
    {
        Assert::maxLength($value, 200, \sprintf('Directive ID "%s" exceeds maximum length of 200 characters.', $value));

        // Strip all UUID suffixes to get the base part
        // UUID format is 36 chars: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        $basePart = $value;
        while (\strlen($basePart) > 37) {
            $potentialUuid = substr($basePart, -36);
            if (1 === preg_match(self::UUID_PATTERN, $potentialUuid) && '-' === $basePart[\strlen($basePart) - 37]) {
                $basePart = substr($basePart, 0, \strlen($basePart) - 37);
            } else {
                break; // @codeCoverageIgnore
            }
        }

        // If we stripped any UUIDs, just validate base is not empty
        if ($basePart !== $value) {
            Assert::notEmpty($basePart, 'Directive ID must have a non-empty base part.');

            return;
        }

        // Standard ID validation
        Assert::kebabCase($value, \sprintf('Directive ID "%s" is not in kebab-case.', $value));
    }

    public function suffix(): self
    {
        return self::fromString(u($this->value)->append('-')->append(Uuid::v7()->toRfc4122())->toString());
    }
}
