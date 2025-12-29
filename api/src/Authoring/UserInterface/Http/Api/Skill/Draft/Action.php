<?php

declare(strict_types=1);

namespace Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft;

use Dairectiv\Authoring\Application\Skill\Draft;
use Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload\ExamplePayload;
use Dairectiv\Authoring\UserInterface\Http\Api\Skill\Draft\Payload\Payload;
use Dairectiv\SharedKernel\Application\Command\CommandBus;
use Dairectiv\SharedKernel\Domain\Object\Assert;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/skills', name: 'draft', methods: ['POST'])]
final class Action extends AbstractController
{
    public function __construct(private readonly CommandBus $commandBus)
    {
    }

    public function __invoke(#[MapRequestPayload] Payload $payload): JsonResponse
    {
        $output = $this->commandBus->execute(
            new Draft\Input(
                $payload->id,
                $payload->name,
                $payload->description,
                $payload->content,
                $payload->workflow->toState(),
                array_map(
                    static fn (ExamplePayload $examplePayload): array => $examplePayload->toState(),
                    $payload->examples,
                ),
            ),
        );

        Assert::isInstanceOf($output, Draft\Output::class);

        return new JsonResponse(Response::fromSkill($output->skill), \Symfony\Component\HttpFoundation\Response::HTTP_CREATED);
    }
}
