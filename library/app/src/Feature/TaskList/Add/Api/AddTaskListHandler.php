<?php

declare(strict_types=1);

namespace Library\Feature\TaskList\Add\Api;

use Library\Feature\TaskList\Add\AddTaskListService;
use Library\Infrastructure\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class AddTaskListHandler
{
    public function __construct(private AddTaskListService $taskListService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $inputData = $request->getAttribute('Data');
        $user = $request->getAttribute('User');

//        if (empty($inputData['title']) || empty($user)) {
//            return Response::create(400);
//        }

        $taskList = $this->taskListService->add($user, $inputData['title']);

        return Response::create(201, $taskList->toArray());
    }
}
