<?php

declare(strict_types=1);

namespace Library\Feature\Task\Delete\All\Api;

use Library\Feature\Task\Delete\DeleteTaskService;
use Library\Infrastructure\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

final class DeleteTaskHandler
{
    public function __construct(private DeleteTaskService $taskService)
    {
    }

    public function __invoke(ServerRequestInterface $request, string $guid): ResponseInterface
    {
        $user = $request->getAttribute('User');

        if (empty($user) || !Uuid::isValid($guid)) {
            return Response::create(404);
        }

        $process = $this->taskService->deleteAll($guid, $user);

        if (!$process) {
            return Response::create(404);
        }

        return Response::create(204);
    }
}
