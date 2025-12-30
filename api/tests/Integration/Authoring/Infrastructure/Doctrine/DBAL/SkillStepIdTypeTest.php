<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Object\Skill\Workflow\StepId;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-type')]
final class SkillStepIdTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'authoring_skill_step_id';

    /**
     * @return iterable<string, array{phpValue: ?StepId, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'step id value' => [
            'phpValue'      => StepId::generate(true),
            'databaseValue' => null,
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?StepId $phpValue, ?string $databaseValue): void
    {
        $databaseValue = $databaseValue ?? $phpValue?->toRfc4122();

        self::assertConvertToDatabaseValue(
            $databaseValue,
            $phpValue,
            self::TYPE,
        );
        self::assertConvertToPhpValue(
            $phpValue,
            $databaseValue,
            self::TYPE,
        );
    }
}
