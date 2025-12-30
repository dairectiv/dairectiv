<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\Domain\Repository;

use Dairectiv\Authoring\Domain\Object\Directive\DirectiveId;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\Domain\Object\Workflow\Workflow;

interface WorkflowRepository
{
    public function save(Workflow $workflow): void;

    /**
     * @throws WorkflowNotFoundException
     */
    public function getWorkflowById(DirectiveId $id): Workflow;
}
