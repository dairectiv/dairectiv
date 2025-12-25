<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\ObjectValue;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

final class ObjectValueType extends JsonType
{
    public function convertToPHPValue($value, AbstractPlatform $platform): ?ObjectValue
    {
        if (null === $value) {
            return null;
        }

        /** @var array{class: class-string<ObjectValue>, state: array<string, mixed>} $value */
        $value = parent::convertToPHPValue($value, $platform);

        return $value['class']::fromArray($value['state']);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        Assert::isInstanceOf($value, ObjectValue::class);

        return parent::convertToDatabaseValue([
            'class' => $value::class,
            'state' => $value->toArray(),
        ], $platform);
    }
}
