<?php

declare(strict_types=1);

namespace Library\Feature\Task\Add;

use Library\Feature\Task;
use Library\Feature\TaskList;
use Library\Feature\TaskListRepository;
use Library\Feature\TaskRepository;
use Library\Feature\User;

final class AddTaskService
{
    public function __construct(private TaskRepository $taskRepository, private TaskListRepository $taskListRepository)
    {
    }

    public function add(array $inputData, User $user): ?Task
    {
        $task = Task::create($inputData['title']);

        if (empty($inputData['tasklist'])) {
            return $this->addToDefault($user, $task);
        }

        $taskList = $this->taskListRepository->findByGuid($inputData['tasklist']);

        if ($this->taskListRepository->isItYourList($user, $taskList)) {
            return $this->addToDefault($user, $task);
        }

        $addedTask = $this->taskRepository->add($task, $taskList);

        if (empty($addedTask)) {
            return null;
        }

        return $addedTask;
    }

    /**
     * @todo use transaction
     */
    private function addToDefault(User $user, Task $task): Task
    {
        $taskList = $this->taskListRepository->findDefault($user);

        if ($taskList instanceof TaskList) {
            return $this->taskRepository->add($task, $taskList);
        }

        $taskList = $this->taskListRepository->add(TaskList::createDefault($user));

        return $this->taskRepository->add($task, $taskList);
    }
}
