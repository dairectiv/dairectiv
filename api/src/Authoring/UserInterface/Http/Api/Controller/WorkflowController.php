<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Controller;

use Dairectiv\Authoring\Application\Directive\Archive;
use Dairectiv\Authoring\Application\Directive\Delete;
use Dairectiv\Authoring\Application\Directive\Publish;
use Dairectiv\Authoring\Application\Workflow\AddExample;
use Dairectiv\Authoring\Application\Workflow\AddStep;
use Dairectiv\Authoring\Application\Workflow\Draft;
use Dairectiv\Authoring\Application\Workflow\Get;
use Dairectiv\Authoring\Application\Workflow\ListWorkflows;
use Dairectiv\Authoring\Application\Workflow\MoveStep;
use Dairectiv\Authoring\Application\Workflow\RemoveExample;
use Dairectiv\Authoring\Application\Workflow\RemoveStep;
use Dairectiv\Authoring\Application\Workflow\Update;
use Dairectiv\Authoring\Application\Workflow\UpdateExample;
use Dairectiv\Authoring\Application\Workflow\UpdateStep;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\AddWorkflowExample\AddWorkflowExamplePayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\AddWorkflowStep\AddWorkflowStepPayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\DraftWorkflow\DraftWorkflowPayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\MoveWorkflowStep\MoveWorkflowStepPayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\UpdateWorkflow\UpdateWorkflowPayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\UpdateWorkflowExample\UpdateWorkflowExamplePayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\UpdateWorkflowStep\UpdateWorkflowStepPayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow\WorkflowResponse;
use Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow\WorkflowsCollectionResponse;
use Dairectiv\SharedKernel\Application\Command\CommandBus;
use Dairectiv\SharedKernel\Application\Query\QueryBus;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/workflows', name: 'workflow_')]
final class WorkflowController extends AbstractController
{
    public function __construct(
        private readonly QueryBus $queryBus,
        private readonly CommandBus $commandBus,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $output = $this->queryBus->fetch(new ListWorkflows\Input(
            page: max(1, $request->query->getInt('page', 1)),
            limit: min(100, max(1, $request->query->getInt('limit', 20))),
            search: $request->query->get('search'),
            state: $request->query->get('state'),
            sortBy: $request->query->get('sortBy', 'createdAt'),
            sortOrder: $request->query->get('sortOrder', 'desc'),
        ));

        Assert::isInstanceOf($output, ListWorkflows\Output::class);

        return $this->json(WorkflowsCollectionResponse::fromOutput($output));
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

    #[Route('/{id}', name: 'update', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['PATCH'])]
    public function update(string $id, #[MapRequestPayload] UpdateWorkflowPayload $payload): JsonResponse
    {
        try {
            $this->commandBus->execute(new Update\Input(
                $id,
                $payload->name,
                $payload->description,
                $payload->content,
            ));

            $output = $this->queryBus->fetch(new Get\Input($id));

            Assert::isInstanceOf($output, Get\Output::class);

            return $this->json(WorkflowResponse::fromWorkflow($output->workflow));
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['PUT'])]
    public function publish(string $id): Response
    {
        try {
            // First verify the workflow exists (throws WorkflowNotFoundException if not found or not a Workflow)
            $this->queryBus->fetch(new Get\Input($id));

            // Then publish it
            $this->commandBus->execute(new Publish\Input($id));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/archive', name: 'archive', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['PUT'])]
    public function archive(string $id): Response
    {
        try {
            // First verify the workflow exists (throws WorkflowNotFoundException if not found or not a Workflow)
            $this->queryBus->fetch(new Get\Input($id));

            // Then archive it
            $this->commandBus->execute(new Archive\Input($id));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['DELETE'])]
    public function delete(string $id): Response
    {
        try {
            // First verify the workflow exists (throws WorkflowNotFoundException if not found or not a Workflow)
            $this->queryBus->fetch(new Get\Input($id));

            // Then delete it (soft delete)
            $this->commandBus->execute(new Delete\Input($id));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) { // @codeCoverageIgnore
            throw new ConflictHttpException($e->getMessage(), $e); // @codeCoverageIgnore
        }
    }

    #[Route('/{id}/examples', name: 'add_example', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['POST'])]
    public function addExample(string $id, #[MapRequestPayload] AddWorkflowExamplePayload $payload): Response
    {
        try {
            $output = $this->commandBus->execute(new AddExample\Input(
                $id,
                $payload->scenario,
                $payload->input,
                $payload->output,
                $payload->explanation,
            ));

            Assert::isInstanceOf($output, AddExample\Output::class);

            $exampleId = $output->example->id->toString();
            $workflowUrl = $this->generateUrl(
                'api_authoring_workflow_get',
                ['id' => $id],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            return new Response(
                null,
                Response::HTTP_CREATED,
                ['Location' => \sprintf('%s/examples/%s', $workflowUrl, $exampleId)],
            );
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/examples/{exampleId}', name: 'update_example', requirements: ['id' => '^[a-z0-9-]+$', 'exampleId' => '^[a-z0-9-]+$'], methods: ['PATCH'])]
    public function updateExample(
        string $id,
        string $exampleId,
        #[MapRequestPayload] UpdateWorkflowExamplePayload $payload,
    ): Response {
        try {
            $this->commandBus->execute(new UpdateExample\Input(
                $id,
                $exampleId,
                $payload->scenario,
                $payload->input,
                $payload->output,
                $payload->explanation,
            ));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/examples/{exampleId}', name: 'remove_example', requirements: ['id' => '^[a-z0-9-]+$', 'exampleId' => '^[a-z0-9-]+$'], methods: ['DELETE'])]
    public function removeExample(string $id, string $exampleId): Response
    {
        try {
            $this->commandBus->execute(new RemoveExample\Input($id, $exampleId));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/steps', name: 'add_step', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['POST'])]
    public function addStep(string $id, #[MapRequestPayload] AddWorkflowStepPayload $payload): Response
    {
        try {
            $output = $this->commandBus->execute(new AddStep\Input(
                $id,
                $payload->content,
                $payload->afterStepId,
            ));

            Assert::isInstanceOf($output, AddStep\Output::class);

            $stepId = $output->step->id->toString();
            $workflowUrl = $this->generateUrl(
                'api_authoring_workflow_get',
                ['id' => $id],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            return new Response(
                null,
                Response::HTTP_CREATED,
                ['Location' => \sprintf('%s/steps/%s', $workflowUrl, $stepId)],
            );
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/steps/{stepId}', name: 'update_step', requirements: ['id' => '^[a-z0-9-]+$', 'stepId' => '^[a-z0-9-]+$'], methods: ['PATCH'])]
    public function updateStep(
        string $id,
        string $stepId,
        #[MapRequestPayload] UpdateWorkflowStepPayload $payload,
    ): Response {
        try {
            $this->commandBus->execute(new UpdateStep\Input(
                $id,
                $stepId,
                $payload->content,
            ));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/steps/{stepId}/move', name: 'move_step', requirements: ['id' => '^[a-z0-9-]+$', 'stepId' => '^[a-z0-9-]+$'], methods: ['PUT'])]
    public function moveStep(
        string $id,
        string $stepId,
        #[MapRequestPayload] MoveWorkflowStepPayload $payload,
    ): Response {
        try {
            // Verify the workflow exists
            $this->queryBus->fetch(new Get\Input($id));

            $this->commandBus->execute(new MoveStep\Input(
                $id,
                $stepId,
                $payload->afterStepId,
            ));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/steps/{stepId}', name: 'remove_step', requirements: ['id' => '^[a-z0-9-]+$', 'stepId' => '^[a-z0-9-]+$'], methods: ['DELETE'])]
    public function removeStep(string $id, string $stepId): Response
    {
        try {
            $this->commandBus->execute(new RemoveStep\Input($id, $stepId));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }
}
