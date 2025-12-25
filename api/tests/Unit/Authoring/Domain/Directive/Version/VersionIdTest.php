<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Directive\Version;

use Dairectiv\Authoring\Domain\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Directive\Metadata\DirectiveName;
use Dairectiv\Authoring\Domain\Directive\Version\VersionId;
use Dairectiv\Authoring\Domain\Directive\Version\VersionNumber;
use Dairectiv\Authoring\Domain\Rule\Rule;
use Dairectiv\Authoring\Domain\Rule\RuleContent;
use PHPUnit\Framework\TestCase;

final class VersionIdTest extends TestCase
{
    public function testItShouldCreateVersionIdFromDirectiveAndVersionNumber(): void
    {
        $rule = $this->createRule('my-rule');
        $versionNumber = VersionNumber::initial();

        $versionId = VersionId::create($rule, $versionNumber);

        self::assertSame('my-rule-v1', $versionId->id);
    }

    public function testItShouldCreateDifferentIdForDifferentVersionNumbers(): void
    {
        $rule = $this->createRule('my-rule');

        $versionId1 = VersionId::create($rule, VersionNumber::initial());
        $versionId2 = VersionId::create($rule, VersionNumber::initial()->increment());

        self::assertSame('my-rule-v1', $versionId1->id);
        self::assertSame('my-rule-v2', $versionId2->id);
    }

    public function testItShouldReturnIdAsStringWhenCastToString(): void
    {
        $rule = $this->createRule('test-directive');
        $versionNumber = VersionNumber::initial();

        $versionId = VersionId::create($rule, $versionNumber);

        self::assertSame('test-directive-v1', (string) $versionId);
    }

    public function testItShouldBeEqualToAnotherVersionIdWithSameValue(): void
    {
        $rule = $this->createRule('same-rule');
        $versionNumber = VersionNumber::initial();

        $versionId1 = VersionId::create($rule, $versionNumber);
        $versionId2 = VersionId::create($rule, $versionNumber);

        self::assertTrue($versionId1->equals($versionId2));
    }

    public function testItShouldNotBeEqualToAnotherVersionIdWithDifferentValue(): void
    {
        $rule = $this->createRule('my-rule');

        $versionId1 = VersionId::create($rule, VersionNumber::initial());
        $versionId2 = VersionId::create($rule, VersionNumber::initial()->increment());

        self::assertFalse($versionId1->equals($versionId2));
    }

    private function createRule(string $id): Rule
    {
        return Rule::draft(
            DirectiveId::fromString($id),
            $this->createMetadata(),
            RuleContent::fromString('Test content'),
        );
    }

    private function createMetadata(): DirectiveMetadata
    {
        return DirectiveMetadata::create(
            DirectiveName::fromString('test-name'),
            DirectiveDescription::fromString('Test description'),
        );
    }
}
