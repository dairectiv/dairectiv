<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload;

use Symfony\Component\Serializer\Attribute\DiscriminatorMap;
use Symfony\Component\Validator\Constraints;

#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: [
        'sequential' => SequentialWorkflowPayload::class,
        'template'   => TemplateWorkflowPayload::class,
        'checklist'  => ChecklistWorkflowPayload::class,
        'hybrid'     => HybridWorkflowPayload::class,
    ],
)]
abstract class WorkflowPayload
{
    /**
     * @var 'sequential'|'template'|'checklist'|'hybrid'
     */
    #[Constraints\Choice(choices: ['sequential', 'template', 'checklist', 'hybrid'])]
    public string $type;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;

    /**
     * @return array{
     *      type: 'sequential'|'template'|'checklist'|'hybrid',
     *      steps?: list<array{order: int, title: string, content: string, type: string, condition?: ?string}>,
     *      templates?: list<array{name: string, content: string, description?: ?string}>,
     *      items?: list<array{order: int, title: string, content: string, type: string, condition?: ?string}>
     *  }
     */
    final public function toState(): array
    {
        /**
         * @var array{
         *      steps?: list<array{order: int, title: string, content: string, type: string, condition?: ?string}>,
         *      templates?: list<array{name: string, content: string, description?: ?string}>,
         *      items?: list<array{order: int, title: string, content: string, type: string, condition?: ?string}>
         *  } $workflow
         */
        $workflow = $this->toArray();

        return [
            'type' => $this->type,
            ...$workflow,
        ];
    }
}
