<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Factory\Rule;

use Cake\Chronos\Chronos;
use Dairectiv\Authoring\Domain\Object\Rule\RuleExample;
use Dairectiv\Authoring\Domain\Repository\DirectiveRepository;
use Dairectiv\Authoring\Infrastructure\Zenstruck\Foundry\Dto\RuleDto;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @extends ObjectFactory<RuleDto>
 */
final class RuleFactory extends ObjectFactory
{
    public function __construct(private readonly DirectiveRepository $directiveRepository)
    {
        parent::__construct();
    }

    private function persist(RuleDto $ruleDto): void
    {
        $this->directiveRepository->save($ruleDto->build());
    }

    protected function defaults(): array
    {
        return [
            'id'          => 'rule-foo',
            'name'        => 'Foo Rule',
            'description' => 'This is a foo rule.',
            'createdAt'   => Chronos::now(),
            'updatedAt'   => Chronos::now(),
            'content'     => 'This is the content of the foo rule.',
            'examples'    => RuleExampleFactory::new()->many(3),
        ];
    }

    protected function initialize(): static
    {
        return $this->afterInstantiate($this->persist(...));
    }

    public static function class(): string
    {
        return RuleDto::class;
    }

    /**
     * @param iterable<RuleExample> $examples
     */
    public function withExamples(iterable $examples): self
    {
        return $this->with(['examples' => $examples]);
    }

    public function withId(string $id): self
    {
        return $this->with(['id' => $id]);
    }

    public function withName(string $name): self
    {
        return $this->with(['name' => $name]);
    }

    public function withContent(string $content): self
    {
        return $this->with(['content' => $content]);
    }
}
