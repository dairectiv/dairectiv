<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\IntValue;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class IntValueType extends Type
{
    /**
     * @return class-string<IntValue>
     */
    abstract protected function getIntValueClass(): string;

    public function convertToPHPValue($value, AbstractPlatform $platform): ?IntValue
    {
        if (null === $value) {
            return null;
        }

        Assert::integer($value);

        $class = $this->getIntValueClass();

        return $class::fromInt($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if (null === $value) {
            return null;
        }

        Assert::isInstanceOf($value, IntValue::class);

        return $value->toInt();
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getIntegerTypeDeclarationSQL($column);
    }

    public function getBindingType(): ParameterType
    {
        return ParameterType::INTEGER;
    }
}
