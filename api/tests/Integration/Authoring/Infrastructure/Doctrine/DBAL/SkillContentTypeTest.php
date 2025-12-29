<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Infrastructure\Doctrine\DBAL;

use Dairectiv\Authoring\Domain\Object\Skill\SkillContent;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
#[Group('authoring')]
#[Group('doctrine-type')]
final class SkillContentTypeTest extends IntegrationTestCase
{
    private const string TYPE = 'authoring_skill_content';

    /**
     * @return iterable<string, array{phpValue: ?SkillContent, databaseValue: ?string}>
     */
    public static function provideValidValues(): iterable
    {
        yield 'nullable value' => [
            'phpValue'      => null,
            'databaseValue' => null,
        ];
        yield 'content value' => [
            'phpValue'      => SkillContent::fromString('## When to Use'),
            'databaseValue' => '## When to Use',
        ];
    }

    #[DataProvider('provideValidValues')]
    public function testItShouldConvertValueInBothWays(?SkillContent $phpValue, ?string $databaseValue): void
    {
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
