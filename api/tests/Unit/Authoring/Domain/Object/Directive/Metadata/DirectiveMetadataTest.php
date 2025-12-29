<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Unit\Authoring\Domain\Object\Directive\Metadata;

use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveDescription;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveMetadata;
use Dairectiv\Authoring\Domain\Object\Directive\Metadata\DirectiveName;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
#[Group('authoring')]
final class DirectiveMetadataTest extends TestCase
{
    public function testItShouldCreateMetadataFromNameAndDescription(): void
    {
        $name = DirectiveName::fromString('my-directive');
        $description = DirectiveDescription::fromString('A directive description');

        $metadata = DirectiveMetadata::create($name, $description);

        self::assertSame($name, $metadata->name);
        self::assertSame($description, $metadata->description);
    }

    public function testItShouldUpdateNameViaWith(): void
    {
        $originalName = DirectiveName::fromString('original-name');
        $description = DirectiveDescription::fromString('A description');
        $metadata = DirectiveMetadata::create($originalName, $description);

        $newName = DirectiveName::fromString('new-name');
        $updatedMetadata = $metadata->with($newName, null);

        self::assertSame($newName, $updatedMetadata->name);
        self::assertSame($description, $updatedMetadata->description);
    }

    public function testItShouldUpdateDescriptionViaWith(): void
    {
        $name = DirectiveName::fromString('my-name');
        $originalDescription = DirectiveDescription::fromString('Original description');
        $metadata = DirectiveMetadata::create($name, $originalDescription);

        $newDescription = DirectiveDescription::fromString('New description');
        $updatedMetadata = $metadata->with(null, $newDescription);

        self::assertSame($name, $updatedMetadata->name);
        self::assertSame($newDescription, $updatedMetadata->description);
    }

    public function testItShouldUpdateBothNameAndDescriptionViaWith(): void
    {
        $originalName = DirectiveName::fromString('original-name');
        $originalDescription = DirectiveDescription::fromString('Original description');
        $metadata = DirectiveMetadata::create($originalName, $originalDescription);

        $newName = DirectiveName::fromString('new-name');
        $newDescription = DirectiveDescription::fromString('New description');
        $updatedMetadata = $metadata->with($newName, $newDescription);

        self::assertSame($newName, $updatedMetadata->name);
        self::assertSame($newDescription, $updatedMetadata->description);
    }

    public function testItShouldBeImmutableWhenUpdating(): void
    {
        $originalName = DirectiveName::fromString('original-name');
        $originalDescription = DirectiveDescription::fromString('Original description');
        $metadata = DirectiveMetadata::create($originalName, $originalDescription);

        $metadata->with(
            DirectiveName::fromString('new-name'),
            DirectiveDescription::fromString('New description'),
        );

        self::assertSame($originalName, $metadata->name);
        self::assertSame($originalDescription, $metadata->description);
    }

    public function testItShouldReturnSameValuesWhenWithReceivesNulls(): void
    {
        $name = DirectiveName::fromString('my-name');
        $description = DirectiveDescription::fromString('My description');
        $metadata = DirectiveMetadata::create($name, $description);

        $updatedMetadata = $metadata->with(null, null);

        self::assertSame($name, $updatedMetadata->name);
        self::assertSame($description, $updatedMetadata->description);
    }

    public function testItShouldReturnNewInstanceWhenUpdating(): void
    {
        $metadata = DirectiveMetadata::create(
            DirectiveName::fromString('original'),
            DirectiveDescription::fromString('Original description'),
        );

        $updatedMetadata = $metadata->with(
            DirectiveName::fromString('new'),
            null,
        );

        self::assertNotSame($metadata, $updatedMetadata);
    }
}
