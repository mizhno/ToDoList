<?php

declare(strict_types=1);

namespace Library\Feature\Task\Rename\Api;

use Library\Feature\Task\Rename\RenameTaskService;
use Library\Infrastructure\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

final class RenameTaskHandler
{
    public function __construct(private RenameTaskService $taskService)
    {
    }

    public function __invoke(ServerRequestInterface $request, string $guid): ResponseInterface
    {
        $inputData = $request->getAttribute('Data');

        if (empty($inputData['title']) || !Uuid::isValid($guid)) {
            return Response::create(404);
        }

        $task = $this->taskService->rename($guid, $inputData['title']);

        if ($task === null) {
            return Response::create(404);
        }

        return Response::create(201, $task->toArray());
    }
}
