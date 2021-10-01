<?php

declare(strict_types=1);

namespace Library\Feature\TaskList\Add;

use DomainException;
use Library\Feature\TaskList;
use Library\Feature\TaskListRepository;
use Library\Feature\User;

final class AddTaskListService
{
    public function __construct(private TaskListRepository $taskListRepository)
    {
    }

    public function add(User $user, string $title): TaskList
    {
        $taskList = TaskList::create($title, $user);

        $addedTasklist = $this->taskListRepository->add($taskList);

        if (empty($addedTasklist)) {
            throw new DomainException('Something went wrong');
        }

        return $taskList;
    }
}
