<?php

declare(strict_types=1);

namespace Library\Feature\Task\Add\Api;

use Library\Feature\Task\Add\AddTaskService;
use Library\Infrastructure\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AddTaskHandler
{
    public function __construct(private AddTaskService $taskService)
    {
    }

    /**
     * @throws \JsonException
     *
     * @todo Валидация для title: если пустой возвращать сообщение об ошибке
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $inputData = $request->getAttribute('Data');
        $user = $request->getAttribute('User');

        if (empty($inputData['title']) || empty($user)) {
            return Response::create(400);
        }

        $task = $this->taskService->add($inputData, $user);

        if (empty($task)) {
            return Response::create(404);
        }

        return Response::create(201, $task->toArray());
    }
}
