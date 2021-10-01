<?php

declare(strict_types=1);

namespace Library\Feature\Task\Toggle;

use Library\Feature\Task;
use Library\Feature\TaskRepository;

final class ToggleTaskService
{
    public function __construct(private TaskRepository $repository)
    {
    }

    public function toggle(string $guid): ?Task
    {
        $task = $this->repository->findByGuid($guid);

        if ($task === null) {
            return null;
        }

        return $this->repository->save($task->toggle());
    }
}
