<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Controller;

use Dairectiv\Authoring\Application\Directive\Archive;
use Dairectiv\Authoring\Application\Directive\Delete;
use Dairectiv\Authoring\Application\Directive\Publish;
use Dairectiv\Authoring\Application\Rule\AddExample;
use Dairectiv\Authoring\Application\Rule\Draft;
use Dairectiv\Authoring\Application\Rule\Get;
use Dairectiv\Authoring\Application\Rule\RemoveExample;
use Dairectiv\Authoring\Application\Rule\Update;
use Dairectiv\Authoring\Application\Rule\UpdateExample;
use Dairectiv\Authoring\Domain\Object\Directive\Exception\DirectiveAlreadyExistsException;
use Dairectiv\Authoring\Domain\Object\Rule\Exception\RuleNotFoundException;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Rule\AddRuleExample\AddRuleExamplePayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Rule\DraftRule\DraftRulePayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Rule\UpdateRule\UpdateRulePayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Payload\Rule\UpdateRuleExample\UpdateRuleExamplePayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Response\Rule\RuleResponse;
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

#[Route('/rules', name: 'rule_')]
final class RuleController extends AbstractController
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

            return $this->json(RuleResponse::fromRule($output->rule));
        } catch (RuleNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }

    #[Route('', name: 'draft', methods: ['POST'])]
    public function draft(#[MapRequestPayload] DraftRulePayload $payload): JsonResponse
    {
        try {
            $output = $this->commandBus->execute(new Draft\Input($payload->name, $payload->description));

            Assert::isInstanceOf($output, Draft\Output::class);

            return $this->json(RuleResponse::fromRule($output->rule), 201);
        } catch (DirectiveAlreadyExistsException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}', name: 'update', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['PATCH'])]
    public function update(string $id, #[MapRequestPayload] UpdateRulePayload $payload): JsonResponse
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

            return $this->json(RuleResponse::fromRule($output->rule));
        } catch (RuleNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/publish', name: 'publish', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['PUT'])]
    public function publish(string $id): Response
    {
        try {
            // First verify the rule exists (throws RuleNotFoundException if not found or not a Rule)
            $this->queryBus->fetch(new Get\Input($id));

            // Then publish it
            $this->commandBus->execute(new Publish\Input($id));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (RuleNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/archive', name: 'archive', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['PUT'])]
    public function archive(string $id): Response
    {
        try {
            // First verify the rule exists (throws RuleNotFoundException if not found or not a Rule)
            $this->queryBus->fetch(new Get\Input($id));

            // Then archive it
            $this->commandBus->execute(new Archive\Input($id));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (RuleNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new ConflictHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}', name: 'delete', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['DELETE'])]
    public function delete(string $id): Response
    {
        try {
            // First verify the rule exists (throws RuleNotFoundException if not found or not a Rule)
            $this->queryBus->fetch(new Get\Input($id));

            // Then delete it (soft delete)
            $this->commandBus->execute(new Delete\Input($id));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (RuleNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) { // @codeCoverageIgnore
            throw new ConflictHttpException($e->getMessage(), $e); // @codeCoverageIgnore
        }
    }

    #[Route('/{id}/examples', name: 'add_example', requirements: ['id' => '^[a-z0-9-]+$'], methods: ['POST'])]
    public function addExample(string $id, #[MapRequestPayload] AddRuleExamplePayload $payload): Response
    {
        try {
            $output = $this->commandBus->execute(new AddExample\Input(
                $id,
                $payload->good,
                $payload->bad,
                $payload->explanation,
            ));

            Assert::isInstanceOf($output, AddExample\Output::class);

            $exampleId = $output->example->id->toString();
            $ruleUrl = $this->generateUrl(
                'api_authoring_rule_get',
                ['id' => $id],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            return new Response(
                null,
                Response::HTTP_CREATED,
                ['Location' => \sprintf('%s/examples/%s', $ruleUrl, $exampleId)],
            );
        } catch (RuleNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }
    }

    #[Route('/{id}/examples/{exampleId}', name: 'update_example', requirements: ['id' => '^[a-z0-9-]+$', 'exampleId' => '^[a-z0-9-]+$'], methods: ['PATCH'])]
    public function updateExample(
        string $id,
        string $exampleId,
        #[MapRequestPayload] UpdateRuleExamplePayload $payload,
    ): Response {
        try {
            $this->commandBus->execute(new UpdateExample\Input(
                $id,
                $exampleId,
                $payload->good,
                $payload->bad,
                $payload->explanation,
            ));

            return new Response(null, Response::HTTP_NO_CONTENT);
        } catch (RuleNotFoundException $e) {
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
        } catch (RuleNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }
    }
}
