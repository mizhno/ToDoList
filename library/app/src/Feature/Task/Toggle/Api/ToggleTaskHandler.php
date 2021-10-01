<?php

declare(strict_types=1);

namespace Library\Feature\Task\Toggle\Api;

use Library\Feature\Task\Toggle\ToggleTaskService;
use Library\Infrastructure\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

final class ToggleTaskHandler
{
    public function __construct(private ToggleTaskService $taskService)
    {
    }

    public function __invoke(ServerRequestInterface $request, string $guid): ResponseInterface
    {
        if (!Uuid::isValid($guid)) {
            return Response::create(404);
        }

        $task = $this->taskService->toggle($guid);

        if ($task === null) {
            return Response::create(404);
        }

        return Response::create(201, $task->toArray());
    }
}
