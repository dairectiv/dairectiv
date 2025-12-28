<?php

declare(strict_types=1);

namespace Dairectiv\Tests\Integration\Authoring\Application\Skill;

use Dairectiv\Authoring\Application\Skill\Update\Input;
use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Directive\Event\DirectiveUpdated;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveNotFoundException;
use Dairectiv\Authoring\Domain\Object\Skill\Skill;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\TemplateWorkflow;
use Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\SkillFactory;
use Dairectiv\Tests\Framework\IntegrationTestCase;
use Zenstruck\Foundry\Test\Factories;

final class UpdateTest extends IntegrationTestCase
{
    use Factories;

    public function testItShouldUpdateMetadataOnly(): void
    {
        $skill = SkillFactory::new()->with(['id' => 'skill-to-update'])->create();

        $this->execute(new Input(
            id: $skill->id,
            name: 'Updated Name',
            description: 'Updated Description',
        ));

        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString($skill->id)], true);

        self::assertSame('Updated Name', (string) $skill->metadata->name);
        self::assertSame('Updated Description', (string) $skill->metadata->description);
    }

    public function testItShouldUpdateContentOnly(): void
    {
        $skill = SkillFactory::new()->with(['id' => 'skill-to-update'])->create();

        $this->execute(new Input(
            id: $skill->id,
            content: 'Updated Content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString($skill->id)], true);

        self::assertSame('Updated Content', (string) $skill->content);
    }

    public function testItShouldUpdateWorkflow(): void
    {
        $skill = SkillFactory::new()->with(['id' => 'skill-to-update'])->create();

        $this->execute(new Input(
            id: $skill->id,
            workflow: [
                'type'      => 'template',
                'templates' => [
                    ['name' => 'Template 1', 'content' => 'Content 1', 'description' => 'Description 1'],
                    ['name' => 'Template 2', 'content' => 'Content 2'],
                ],
            ],
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString($skill->id)], true);

        self::assertInstanceOf(TemplateWorkflow::class, $skill->workflow);
    }

    public function testItShouldUpdateExamplesOnly(): void
    {
        $skill = SkillFactory::new()->with(['id' => 'skill-to-update'])->create();

        $this->execute(new Input(
            id: $skill->id,
            examples: [
                ['scenario' => 'Scenario 1', 'input' => 'Input 1', 'output' => 'Output 1'],
                ['scenario' => 'Scenario 2', 'input' => 'Input 2', 'output' => 'Output 2', 'explanation' => 'Explanation'],
            ],
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString($skill->id)], true);

        self::assertCount(2, $skill->examples);
    }

    public function testItShouldUpdateBothMetadataAndContent(): void
    {
        $skill = SkillFactory::new()->with(['id' => 'skill-to-update'])->create();

        $this->execute(new Input(
            id: $skill->id,
            name: 'Updated Name',
            content: 'Updated Content',
        ));

        self::assertDomainEventHasBeenDispatched(DirectiveUpdated::class);
        $skill = $this->findEntity(Skill::class, ['id' => DirectiveId::fromString($skill->id)], true);

        self::assertSame('Updated Name', (string) $skill->metadata->name);
        self::assertSame('Updated Content', (string) $skill->content);
    }

    public function testItShouldThrowExceptionWhenSkillNotFound(): void
    {
        $this->expectException(DirectiveNotFoundException::class);

        $this->execute(new Input(id: 'non-existent-skill'));
    }
}
