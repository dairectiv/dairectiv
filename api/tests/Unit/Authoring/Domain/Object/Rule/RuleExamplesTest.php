<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Rule;

use Dairectiv\Authoring\Domain\Object\Rule\RuleExample;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExamples;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('authoring')]
final class RuleExamplesTest extends TestCase
{
    public function testItShouldCreateEmptyCollection(): void
    {
        $examples = RuleExamples::empty();

        self::assertCount(0, $examples);
        self::assertTrue($examples->isEmpty());
    }

    public function testItShouldCreateCollectionFromArray(): void
    {
        $example1 = RuleExample::good('code1');
        $example2 = RuleExample::bad('code2');

        $examples = RuleExamples::fromList([$example1, $example2]);

        self::assertCount(2, $examples);
        self::assertFalse($examples->isEmpty());
    }

    public function testItShouldThrowExceptionWhenArrayContainsNonRuleExample(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('All examples must be RuleExample instances.');

        /** @phpstan-ignore argument.type */
        RuleExamples::fromList(['not a RuleExample']);
    }

    public function testItShouldAddExampleToCollection(): void
    {
        $examples = RuleExamples::empty();
        $example = RuleExample::good('code');

        $newExamples = $examples->add($example);

        self::assertCount(0, $examples);
        self::assertCount(1, $newExamples);
    }

    public function testItShouldBeImmutableWhenAdding(): void
    {
        $examples = RuleExamples::empty();
        $example = RuleExample::good('code');

        $newExamples = $examples->add($example);

        self::assertNotSame($examples, $newExamples);
        self::assertTrue($examples->isEmpty());
        self::assertFalse($newExamples->isEmpty());
    }

    public function testItShouldBeIterable(): void
    {
        $example1 = RuleExample::good('code1');
        $example2 = RuleExample::bad('code2');

        $examples = RuleExamples::fromList([$example1, $example2]);

        $items = [];
        foreach ($examples as $example) {
            $items[] = $example;
        }

        self::assertCount(2, $items);
        self::assertSame($example1, $items[0]);
        self::assertSame($example2, $items[1]);
    }

    public function testItShouldFilterGoodOnlyExamples(): void
    {
        $goodOnly = RuleExample::good('good code');
        $badOnly = RuleExample::bad('bad code');
        $transformation = RuleExample::transformation('bad', 'good');

        $examples = RuleExamples::fromList([$goodOnly, $badOnly, $transformation]);

        $goods = $examples->goods();

        self::assertCount(1, $goods);
        self::assertSame($goodOnly, $goods[0]);
    }

    public function testItShouldFilterBadOnlyExamples(): void
    {
        $goodOnly = RuleExample::good('good code');
        $badOnly = RuleExample::bad('bad code');
        $transformation = RuleExample::transformation('bad', 'good');

        $examples = RuleExamples::fromList([$goodOnly, $badOnly, $transformation]);

        $bads = $examples->bads();

        self::assertCount(1, $bads);
        self::assertSame($badOnly, $bads[0]);
    }

    public function testItShouldFilterTransformationExamples(): void
    {
        $goodOnly = RuleExample::good('good code');
        $badOnly = RuleExample::bad('bad code');
        $transformation1 = RuleExample::transformation('bad1', 'good1');
        $transformation2 = RuleExample::transformation('bad2', 'good2');

        $examples = RuleExamples::fromList([$goodOnly, $badOnly, $transformation1, $transformation2]);

        $transformations = $examples->transformations();

        self::assertCount(2, $transformations);
        self::assertSame($transformation1, $transformations[0]);
        self::assertSame($transformation2, $transformations[1]);
    }

    public function testItShouldReturnEmptyArrayWhenNoGoodOnlyExamples(): void
    {
        $badOnly = RuleExample::bad('bad code');
        $transformation = RuleExample::transformation('bad', 'good');

        $examples = RuleExamples::fromList([$badOnly, $transformation]);

        self::assertCount(0, $examples->goods());
    }

    public function testItShouldReturnEmptyArrayWhenNoBadOnlyExamples(): void
    {
        $goodOnly = RuleExample::good('good code');
        $transformation = RuleExample::transformation('bad', 'good');

        $examples = RuleExamples::fromList([$goodOnly, $transformation]);

        self::assertCount(0, $examples->bads());
    }

    public function testItShouldReturnEmptyArrayWhenNoTransformations(): void
    {
        $goodOnly = RuleExample::good('good code');
        $badOnly = RuleExample::bad('bad code');

        $examples = RuleExamples::fromList([$goodOnly, $badOnly]);

        self::assertCount(0, $examples->transformations());
    }

    public function testItShouldExposeExamplesArray(): void
    {
        $example1 = RuleExample::good('code1');
        $example2 = RuleExample::bad('code2');

        $examples = RuleExamples::fromList([$example1, $example2]);

        self::assertSame([$example1, $example2], $examples->examples);
    }

    public function testItShouldChainMultipleAdds(): void
    {
        $examples = RuleExamples::empty()
            ->add(RuleExample::good('code1'))
            ->add(RuleExample::bad('code2'))
            ->add(RuleExample::transformation('bad', 'good'))
        ;

        self::assertCount(3, $examples);
    }
}
