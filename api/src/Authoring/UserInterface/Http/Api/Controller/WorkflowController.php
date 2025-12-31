<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Controller;

use Dairectiv\Authoring\Application\Workflow\Draft;
use Dairectiv\Authoring\Application\Workflow\Get;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\DraftWorkflow\DraftWorkflowPayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow\WorkflowResponse;
use Dairectiv\SharedKernel\Application\Command\CommandBus;
use Dairectiv\SharedKernel\Application\Query\QueryBus;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/workflows', name: 'workflow_')]
final class WorkflowController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route('/{id}', name: 'get', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        try {
            $output = $this->queryBus->fetch(new Get\Input($id));

            Assert::isInstanceOf($output, Get\Output::class);

            return $this->json(WorkflowResponse::fromWorkflow($output->workflow));
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }

    #[Route('', name: 'draft', methods: ['POST'])]
    public function draft(#[MapRequestPayload] DraftWorkflowPayload $payload): JsonResponse
    {
        try {
            $output = $this->commandBus->execute(new Draft\Input($payload->name, $payload->description));

            Assert::isInstanceOf($output, Draft\Output::class);

            return $this->json(WorkflowResponse::fromWorkflow($output->workflow), Response::HTTP_CREATED);
        } catch (DirectiveAlreadyExistsException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }
    }
}
