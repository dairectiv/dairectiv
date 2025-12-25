<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\SharedKernel\Domain\Assert;
use Dairectiv\SharedKernel\Domain\ValueObject\StringValue;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class StringValueType extends Type
{
    /**
     * @return class-string<StringValue>
     */
    abstract protected function getStringValueClass(): string;

    public function convertToPHPValue($value, AbstractPlatform $platform): ?StringValue
    {
        if (null === $value) {
            return null;
        }

        Assert::string($value);

        $class = $this->getStringValueClass();

        return $class::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        Assert::isInstanceOf($value, StringValue::class);

        return parent::convertToDatabaseValue((string) $value, $platform);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function getBindingType(): ParameterType
    {
        return ParameterType::STRING;
    }
}
