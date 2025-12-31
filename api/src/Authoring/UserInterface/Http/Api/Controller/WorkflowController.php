<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Controller;

use Dairectiv\Authoring\Application\Workflow\AddExample;
use Dairectiv\Authoring\Application\Workflow\Draft;
use Dairectiv\Authoring\Application\Workflow\Get;
use Dairectiv\Authoring\Application\Workflow\Update;
use Dairectiv\Authoring\Application\Workflow\UpdateExample;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Workflow\Example\ExampleId;
use Dairectiv\Authoring\Domain\Object\Workflow\Exception\WorkflowNotFoundException;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\AddWorkflowExample\AddWorkflowExamplePayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\DraftWorkflow\DraftWorkflowPayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\UpdateWorkflow\UpdateWorkflowPayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Workflow\UpdateWorkflowExample\UpdateWorkflowExamplePayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow\ExampleResponse;
use Dairectiv\Authoring\UserInterface\Http\Api\Response\Workflow\WorkflowResponse;
use Dairectiv\SharedKernel\Application\Command\CommandBus;
use Dairectiv\SharedKernel\Application\Query\QueryBus;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Dairectiv\SharedKernel\Domain\Object\Exception\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    #[Route('/{id}/examples', name: 'add_example', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['POST'])]
    public function addExample(string $id, #[MapRequestPayload] AddWorkflowExamplePayload $payload): JsonResponse
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

            return $this->json(
                ExampleResponse::fromExample($output->example),
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
    ): JsonResponse {
        try {
            $this->commandBus->execute(new UpdateExample\Input(
                $id,
                $exampleId,
                $payload->scenario,
                $payload->input,
                $payload->output,
                $payload->explanation,
            ));

            $output = $this->queryBus->fetch(new Get\Input($id));

            Assert::isInstanceOf($output, Get\Output::class);

            $exampleIdValue = ExampleId::fromString($exampleId);
            $example = $output->workflow->examples->filter(
                static fn ($e) => $e->id->equals($exampleIdValue),
            )->first();

            Assert::notFalse($example, \sprintf('Example with ID "%s" not found.', $exampleId));

            return $this->json(ExampleResponse::fromExample($example));
        } catch (WorkflowNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }
}
