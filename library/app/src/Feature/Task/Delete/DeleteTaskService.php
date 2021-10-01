<?php

declare(strict_types=1);

namespace Library\Feature\Task\Delete;

use Library\Feature\TaskListRepository;
use Library\Feature\TaskRepository;
use Library\Feature\User;

final class DeleteTaskService
{
    public function __construct(private TaskRepository $taskRepository, private TaskListRepository $taskListRepository)
    {
    }

    public function deleteAll(string $guid, User $user): bool
    {
        $tasklist = $this->taskListRepository->findByGuid($guid);

        if (empty($tasklist)) {
            return false;
        }

        if (!$this->taskListRepository->isItYourList($user, $tasklist)) {
            return false;
        }

        $this->taskRepository->deleteAll($tasklist);

        return true;
    }

    public function deleteFinished(string $guid, User $user): bool
    {
        $tasklist = $this->taskListRepository->findByGuid($guid);

        if (empty($tasklist)) {
            return false;
        }

        if (!$this->taskListRepository->isItYourList($user, $tasklist)) {
            return false;
        }

        $this->taskRepository->deleteFinished($tasklist);

        return true;
    }
}
