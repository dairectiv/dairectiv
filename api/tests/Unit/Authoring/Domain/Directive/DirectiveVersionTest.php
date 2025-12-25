<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Directive;

use Dairectiv\Authoring\Domain\Directive\DirectiveVersion;
use PHPUnit\Framework\TestCase;

final class DirectiveVersionTest extends TestCase
{
    public function testItShouldReturnVersionOneWhenInitial(): void
    {
        $version = DirectiveVersion::initial();

        self::assertSame(1, $version->version);
    }

    public function testItShouldReturnNewVersionWithIncrementedValue(): void
    {
        $version = DirectiveVersion::initial();
        $incremented = $version->increment();

        self::assertSame(1, $version->version);
        self::assertSame(2, $incremented->version);
        self::assertNotSame($version, $incremented);
    }

    public function testItShouldBeImmutableWhenIncrementing(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = $v1->increment();
        $v3 = $v2->increment();

        self::assertSame(1, $v1->version);
        self::assertSame(2, $v2->version);
        self::assertSame(3, $v3->version);
    }

    public function testItShouldReturnTrueWhenVersionsAreEqual(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = DirectiveVersion::initial();

        self::assertTrue($v1->equals($v2));
        self::assertTrue($v2->equals($v1));
    }

    public function testItShouldReturnFalseWhenVersionsAreDifferent(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = $v1->increment();

        self::assertFalse($v1->equals($v2));
        self::assertFalse($v2->equals($v1));
    }

    public function testItShouldReturnTrueWhenComparingWithItself(): void
    {
        $version = DirectiveVersion::initial();

        self::assertTrue($version->equals($version));
    }

    public function testItShouldReturnTrueWhenVersionIsOlder(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = $v1->increment();

        self::assertTrue($v1->isOlderThan($v2));
    }

    public function testItShouldReturnFalseWhenVersionIsNewerForOlderThan(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = $v1->increment();

        self::assertFalse($v2->isOlderThan($v1));
    }

    public function testItShouldReturnFalseWhenVersionsAreEqualForOlderThan(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = DirectiveVersion::initial();

        self::assertFalse($v1->isOlderThan($v2));
    }

    public function testItShouldReturnTrueWhenVersionIsNewer(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = $v1->increment();

        self::assertTrue($v2->isNewerThan($v1));
    }

    public function testItShouldReturnFalseWhenVersionIsOlderForNewerThan(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = $v1->increment();

        self::assertFalse($v1->isNewerThan($v2));
    }

    public function testItShouldReturnFalseWhenVersionsAreEqualForNewerThan(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = DirectiveVersion::initial();

        self::assertFalse($v1->isNewerThan($v2));
    }

    public function testItShouldReturnFormattedVersionString(): void
    {
        $version = DirectiveVersion::initial();

        self::assertSame('v1', (string) $version);
        self::assertSame('v1', $version->__toString());
    }

    public function testItShouldFormatIncrementedVersionCorrectly(): void
    {
        $v1 = DirectiveVersion::initial();
        $v2 = $v1->increment();
        $v3 = $v2->increment();

        self::assertSame('v1', (string) $v1);
        self::assertSame('v2', (string) $v2);
        self::assertSame('v3', (string) $v3);
    }

    public function testItShouldFormatLargeVersionNumbers(): void
    {
        $version = DirectiveVersion::initial();

        for ($i = 0; $i < 99; ++$i) {
            $version = $version->increment();
        }

        self::assertSame('v100', (string) $version);
        self::assertSame(100, $version->version);
    }
}
