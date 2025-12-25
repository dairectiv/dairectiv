<?php

declare(strict_types=1);

namespace Dairectiv\SharedKernel\Infrastructure\Doctrine\DBAL\Types;

use Cake\Chronos\Chronos;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;

final class ChronosType extends DateTimeImmutableType
{
    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Chronos
    {
        $datetime = parent::convertToPHPValue($value, $platform);

        if (null === $datetime) {
            return null;
        }

        return new Chronos($datetime);
    }
}
