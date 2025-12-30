<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Domain\Object\ValueObject;

use Symfony\Component\Uid\Uuid;

abstract class UuidValue extends Uuid
{
    /**
     * @var array<class-string<UuidValue>, UuidValue[]>
     */
    protected static array $sequence = [];

    final public function __construct(string $uuid, bool $checkVariant = false)
    {
        parent::__construct($uuid, $checkVariant);
    }

    public static function setNext(Uuid | string | null $next = null): static
    {
        if (null === $next) {
            $next = Uuid::v7();
        }

        $next = $next instanceof Uuid ? $next : Uuid::fromString($next);

        if (!isset(static::$sequence[static::class])) {
            self::$sequence[static::class] = [];
        }

        self::$sequence[static::class][] = $next = new static($next->toRfc4122());

        return $next;
    }

    public static function generate(bool $force = false): static
    {
        if (!$force && 0 < \count(self::$sequence[static::class] ?? [])) {
            /** @phpstan-ignore return.type */
            return array_shift(self::$sequence[static::class]);
        }

        return new static(Uuid::v7()->toRfc4122());
    }

    public static function reset(): void
    {
        self::$sequence = []; // @codeCoverageIgnore
    }
}
