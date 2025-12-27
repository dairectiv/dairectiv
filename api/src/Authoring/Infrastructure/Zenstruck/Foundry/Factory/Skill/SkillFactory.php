<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Skill\SkillExample;
use Dairectiv\Authoring\Domain\Object\Skill\Workflow\SkillWorkflow;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Dto\SkillDto;
use Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Skill\Workflow\SkillWorkflowFactory;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @extends ObjectFactory<SkillDto>
 */
final class SkillFactory extends ObjectFactory
{
    public function __construct(private readonly DirectiveRepository $directiveRepository)
    {
        parent::__construct();
    }

    private function persist(SkillDto $skillDto): void
    {
        $this->directiveRepository->save($skillDto->build());
    }

    protected function defaults(): array
    {
        return [
            'id'          => 'skill-foo',
            'name'        => 'Foo Skill',
            'description' => 'This is a foo skill.',
            'createdAt'   => Chronos::now(),
            'updatedAt'   => Chronos::now(),
            'content'     => 'This is the content of the foo skill.',
            'examples'    => SkillExampleFactory::new()->many(3),
            'workflow'    => SkillWorkflowFactory::random(),
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate($this->persist(...));
    }

    public static function class(): string
    {
        return SkillDto::class;
    }

    /**
     * @param iterable<SkillExample> $examples
     */
    public function withExamples(iterable $examples): self
    {
        return $this->with(['examples' => $examples]);
    }

    public function withWorkflow(SkillWorkflow $workflow): self
    {
        return $this->with(['workflow' => $workflow]);
    }
}
