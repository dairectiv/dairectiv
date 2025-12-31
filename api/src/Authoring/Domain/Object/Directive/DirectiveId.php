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

        // Check if this is a suffixed ID (ends with -UUID)
        // UUID format is 36 chars: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        $length = \strlen($value);
        if ($length > 37) { // At least 1 char + '-' + 36 chars UUID
            $potentialUuid = substr($value, -36);
            if (1 === preg_match(self::UUID_PATTERN, $potentialUuid) && '-' === $value[$length - 37]) {
                // This is a suffixed ID, validate the base part only
                $basePart = substr($value, 0, $length - 37);
                if ('' !== $basePart) {
                    Assert::kebabCase($basePart, \sprintf('Directive ID base "%s" is not in kebab-case.', $basePart));
                }

                return;
            }
        }

        // Standard ID validation
        Assert::kebabCase($value, \sprintf('Directive ID "%s" is not in kebab-case.', $value));
    }

    public function suffix(): self
    {
        return self::fromString(u($this->value)->append('-')->append(Uuid::v7()->toRfc4122())->toString());
    }
}
