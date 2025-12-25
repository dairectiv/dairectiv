<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Rule;

use Dairectiv\Authoring\Domain\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Rule\Rule;
use Dairectiv\Authoring\Domain\Rule\RuleContent;
use Dairectiv\Authoring\Domain\Rule\RuleExample;
use Dairectiv\Authoring\Domain\Rule\RuleExamples;
use Dairectiv\Authoring\Domain\Rule\RuleSnapshot;
use PHPUnit\Framework\TestCase;

final class RuleSnapshotTest extends TestCase
{
    public function testItShouldCaptureContentFromRule(): void
    {
        $content = RuleContent::fromString('Test content');
        $rule = $this->createRule(content: $content);

        $snapshot = RuleSnapshot::fromRule($rule);

        self::assertSame($content, $snapshot->content);
    }

    public function testItShouldCaptureExamplesFromRule(): void
    {
        $examples = RuleExamples::fromList([
            RuleExample::good('good code'),
            RuleExample::bad('bad code'),
        ]);
        $rule = $this->createRule(examples: $examples);

        $snapshot = RuleSnapshot::fromRule($rule);

        self::assertSame($examples, $snapshot->examples);
    }

    public function testItShouldCaptureEmptyExamplesFromRule(): void
    {
        $rule = $this->createRule();

        $snapshot = RuleSnapshot::fromRule($rule);

        self::assertTrue($snapshot->examples->isEmpty());
    }

    public function testItShouldBeImmutableAfterCapture(): void
    {
        $rule = $this->createRule(content: RuleContent::fromString('Original content'));

        $snapshot = RuleSnapshot::fromRule($rule);

        $rule->updateContent(content: RuleContent::fromString('Modified content'));

        self::assertSame('Original content', (string) $snapshot->content);
    }

    public function testItShouldCaptureMultipleExamples(): void
    {
        $examples = RuleExamples::fromList([
            RuleExample::good('good code 1'),
            RuleExample::bad('bad code 1'),
            RuleExample::transformation('before', 'after'),
        ]);
        $rule = $this->createRule(examples: $examples);

        $snapshot = RuleSnapshot::fromRule($rule);

        self::assertCount(3, $snapshot->examples);
    }

    private function createRule(
        ?RuleContent $content = null,
        ?RuleExamples $examples = null,
    ): Rule {
        return Rule::draft(
            DirectiveId::fromString('my-rule'),
            DirectiveMetadata::create(
                DirectiveName::fromString('my-rule-name'),
                DirectiveDescription::fromString('Default description'),
            ),
            $content ?? RuleContent::fromString('Default content'),
            $examples,
        );
    }
}
