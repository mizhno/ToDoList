<?php

declare(strict_types=1);

namespace Library\Feature\Task\Rename;

use Library\Feature\Task;
use Library\Feature\TaskRepository;

final class RenameTaskService
{
    public function __construct(private TaskRepository $repository)
    {
    }

    public function rename(string $guid, string $title): ?Task
    {
        $task = $this->repository->findByGuid($guid);

        if ($task === null) {
            return null;
        }

        return $this->repository->save($task->renameTo($title));
    }
}
