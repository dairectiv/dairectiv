<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Rule;

use Dairectiv\Authoring\Domain\Object\Rule\RuleExample;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RuleExampleTest extends TestCase
{
    public function testItShouldCreateExampleWithGoodAndBad(): void
    {
        $example = RuleExample::create(
            good: '$msg = \sprintf("Hello %s", $name);',
            bad: '$msg = "Hello $name";',
            explanation: 'Always use sprintf for formatting',
        );

        self::assertSame('$msg = \sprintf("Hello %s", $name);', $example->good);
        self::assertSame('$msg = "Hello $name";', $example->bad);
        self::assertSame('Always use sprintf for formatting', $example->explanation);
    }

    public function testItShouldCreateExampleWithOnlyGood(): void
    {
        $example = RuleExample::create(good: 'final readonly class MyVO {}');

        self::assertSame('final readonly class MyVO {}', $example->good);
        self::assertNull($example->bad);
        self::assertNull($example->explanation);
    }

    public function testItShouldCreateExampleWithOnlyBad(): void
    {
        $example = RuleExample::create(bad: 'catch (Exception $e) {}');

        self::assertNull($example->good);
        self::assertSame('catch (Exception $e) {}', $example->bad);
        self::assertNull($example->explanation);
    }

    public function testItShouldThrowExceptionWhenNeitherGoodNorBadProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rule example must have at least a good or bad example.');

        RuleExample::create();
    }

    public function testItShouldThrowExceptionWhenOnlyExplanationProvided(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Rule example must have at least a good or bad example.');

        RuleExample::create(explanation: 'Some explanation without code');
    }

    public function testItShouldCreateGoodExampleUsingFactoryMethod(): void
    {
        $example = RuleExample::good('final readonly class MyVO {}', 'Value objects should be immutable');

        self::assertSame('final readonly class MyVO {}', $example->good);
        self::assertNull($example->bad);
        self::assertSame('Value objects should be immutable', $example->explanation);
    }

    public function testItShouldCreateGoodExampleWithoutExplanation(): void
    {
        $example = RuleExample::good('final readonly class MyVO {}');

        self::assertSame('final readonly class MyVO {}', $example->good);
        self::assertNull($example->bad);
        self::assertNull($example->explanation);
    }

    public function testItShouldCreateBadExampleUsingFactoryMethod(): void
    {
        $example = RuleExample::bad('catch (Exception $e) {}', 'Never silently catch exceptions');

        self::assertNull($example->good);
        self::assertSame('catch (Exception $e) {}', $example->bad);
        self::assertSame('Never silently catch exceptions', $example->explanation);
    }

    public function testItShouldCreateBadExampleWithoutExplanation(): void
    {
        $example = RuleExample::bad('catch (Exception $e) {}');

        self::assertNull($example->good);
        self::assertSame('catch (Exception $e) {}', $example->bad);
        self::assertNull($example->explanation);
    }

    public function testItShouldCreateTransformationUsingFactoryMethod(): void
    {
        $example = RuleExample::transformation(
            bad: '$msg = "Hello $name";',
            good: '$msg = \sprintf("Hello %s", $name);',
            explanation: 'Use sprintf instead of interpolation',
        );

        self::assertSame('$msg = \sprintf("Hello %s", $name);', $example->good);
        self::assertSame('$msg = "Hello $name";', $example->bad);
        self::assertSame('Use sprintf instead of interpolation', $example->explanation);
    }

    public function testItShouldReturnTrueForHasGoodWhenGoodExists(): void
    {
        $example = RuleExample::good('code');

        self::assertTrue($example->hasGood());
        self::assertFalse($example->hasBad());
    }

    public function testItShouldReturnTrueForHasBadWhenBadExists(): void
    {
        $example = RuleExample::bad('code');

        self::assertFalse($example->hasGood());
        self::assertTrue($example->hasBad());
    }

    public function testItShouldReturnTrueForIsTransformationWhenBothExist(): void
    {
        $example = RuleExample::transformation('bad', 'good');

        self::assertTrue($example->isTransformation());
        self::assertTrue($example->hasGood());
        self::assertTrue($example->hasBad());
    }

    public function testItShouldReturnFalseForIsTransformationWhenOnlyGood(): void
    {
        $example = RuleExample::good('code');

        self::assertFalse($example->isTransformation());
    }

    public function testItShouldReturnFalseForIsTransformationWhenOnlyBad(): void
    {
        $example = RuleExample::bad('code');

        self::assertFalse($example->isTransformation());
    }

    public function testItShouldBeImmutable(): void
    {
        $example = RuleExample::good('original code', 'original explanation');

        self::assertSame('original code', $example->good);
        self::assertSame('original explanation', $example->explanation);
    }
}
