<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain;

use Dairectiv\SharedKernel\Domain\Exception\InvalidArgumentException;
use Webmozart\Assert\Assert as WebmozartAssert;
use function Symfony\Component\String\u;

final class Assert extends WebmozartAssert
{
    /**
     * @phpstan-assert string $value
     *
     * @throws InvalidArgumentException
     */
    public static function kebabCase(mixed $value, ?string $message = null): string
    {
        Assert::string($value);
        Assert::notEmpty($value);

        $kebabCased = u($value)->kebab()->toString();

        Assert::same(
            $value,
            $kebabCased,
            $message ?? \sprintf('String "%s" is not in kebab-case (e.g. "%s").', $value, $kebabCased),
        );

        return $value;
    }

    protected static function reportInvalidArgument(string $message): never
    {
        throw new InvalidArgumentException($message);
    }
}
