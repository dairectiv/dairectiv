<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Directive\Version;

use Dairectiv\Authoring\Domain\Object\Directive\Version\VersionNumber;
use PHPUnit\Framework\TestCase;

final class VersionNumberTest extends TestCase
{
    public function testItShouldCreateInitialVersionWithValueOne(): void
    {
        $version = VersionNumber::initial();

        self::assertSame(1, $version->number);
    }

    public function testItShouldIncrementVersionByOne(): void
    {
        $version = VersionNumber::initial();

        $incremented = $version->increment();

        self::assertSame(2, $incremented->number);
    }

    public function testItShouldBeImmutableWhenIncrementing(): void
    {
        $version = VersionNumber::initial();

        $version->increment();

        self::assertSame(1, $version->number);
    }

    public function testItShouldAllowMultipleIncrements(): void
    {
        $version = VersionNumber::initial()
            ->increment()
            ->increment()
            ->increment()
        ;

        self::assertSame(4, $version->number);
    }

    public function testItShouldBeEqualToAnotherVersionWithSameValue(): void
    {
        $version1 = VersionNumber::initial();
        $version2 = VersionNumber::initial();

        self::assertTrue($version1->equals($version2));
    }

    public function testItShouldNotBeEqualToAnotherVersionWithDifferentValue(): void
    {
        $version1 = VersionNumber::initial();
        $version2 = VersionNumber::initial()->increment();

        self::assertFalse($version1->equals($version2));
    }

    public function testItShouldDetectOlderVersion(): void
    {
        $older = VersionNumber::initial();
        $newer = $older->increment();

        self::assertTrue($older->isOlderThan($newer));
        self::assertFalse($newer->isOlderThan($older));
    }

    public function testItShouldDetectNewerVersion(): void
    {
        $older = VersionNumber::initial();
        $newer = $older->increment();

        self::assertTrue($newer->isNewerThan($older));
        self::assertFalse($older->isNewerThan($newer));
    }

    public function testItShouldNotBeOlderOrNewerThanItself(): void
    {
        $version = VersionNumber::initial();

        self::assertFalse($version->isOlderThan($version));
        self::assertFalse($version->isNewerThan($version));
    }

    public function testItShouldReturnFormattedStringWhenCastToString(): void
    {
        $version = VersionNumber::initial();

        self::assertSame('v1', (string) $version);
    }

    public function testItShouldReturnFormattedStringForHigherVersions(): void
    {
        $version = VersionNumber::initial()->increment()->increment();

        self::assertSame('v3', (string) $version);
    }
}
