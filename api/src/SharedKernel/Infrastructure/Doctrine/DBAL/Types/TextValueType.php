<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types;

use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\ValueObject\TextValue;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

abstract class TextValueType extends Type
{
    /**
     * @return class-string<TextValue>
     */
    abstract protected function getTextValueClass(): string;

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TextValue
    {
        if (null === $value) {
            return null;
        }

        Assert::string($value);

        $class = $this->getTextValueClass();

        return $class::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value) {
            return null;
        }

        Assert::isInstanceOf($value, TextValue::class);

        return parent::convertToDatabaseValue((string) $value, $platform);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getClobTypeDeclarationSQL($column);
    }

    public function getBindingType(): ParameterType
    {
        return ParameterType::STRING;
    }
}
