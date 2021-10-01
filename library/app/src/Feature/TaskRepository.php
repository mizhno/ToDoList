<?php

declare(strict_types=1);

namespace Library\Feature;

use Library\Infrastructure\Database\Connection;
use Throwable;

final class TaskRepository
{
    public function __construct(private Connection $connection, private UserRepository $userRepository)
    {
    }

    public function add(Task $task, TaskList $taskList): ?Task
    {
        $this->connection->beginTransaction();

        try {
            $this->connection->insert(
                'tasks',
                ['guid', 'title', 'status', 'create_time'],
                [[
                    $task->toArrayForAdd(),
                ]]
            );

            $this->connection->insert(
                'tasklists_tasks',
                ['tasklist', 'task'],
                [[$taskList->guid()->toString(), $task->guid()->toString()]]
            );

            $this->connection->commit();

            return $task;
        } catch (Throwable) {
            $this->connection->rollback();

            return null;
        }
    }

    public function addList(TaskList $tasklist): ?TaskList
    {
        $this->connection->beginTransaction();

        try {
            if ($tasklist->title() === null) {
                $this->connection->insert(
                    'tasklists',
                    ['guid'],
                    [[$tasklist->guid()->toString()]]
                );
            } else {
                $this->connection->insert(
                    'tasklists',
                    ['guid', 'title'],
                    [[$tasklist->guid()->toString(), $tasklist->title()]]
                );
            }

            $this->connection->insert(
                'users_tasklists',
                ['users', 'tasklists'],
                [[$tasklist->user()->guid()->toString(), $tasklist->guid()->toString()]]
            );

            $this->connection->commit();

            return $tasklist;
        } catch (Throwable) {
            $this->connection->rollback();

            return null;
        }
    }

    public function save(Task $task): ?Task
    {
        $this->connection->beginTransaction();

        try {
            $taskArray = $task->toArrayForSave();

            $this->connection->update(
                'tasks',
                [
                    'title' => $taskArray['title'],
                    'status' => $taskArray['status'],
                    'complete_time' => $taskArray['complete_time'] === null ? null : $taskArray['complete_time'],
                ],
                [[
                    'guid' => $taskArray['guid'],
                ]]
            );

            $this->connection->commit();

            return $task;
        } catch (Throwable) {
            $this->connection->rollback();

            return null;
        }
    }

    public function findByGuid(string $guid): ?Task
    {
        $task = $this->connection->selectOneRow('tasks', ['*'], [['guid' => $guid]]);

        if (empty($task)) {
            return null;
        }

        return Task::createFromArray($task);
    }

    public function deleteAll(TaskList $taskList): void
    {
        $tasks = $taskList->tasks();

        $taskGuids = [];

        foreach ($tasks as $task) {
            $taskGuids[] = $task->guid()->toString();
        }

        $this->connection->delete('tasks', [['guid' => $taskGuids]]);
    }

    public function deleteFinished(TaskList $taskList): void
    {
        $tasks = $taskList->tasks();

        $taskGuids = [];

        foreach ($tasks as $task) {
            if ($task->isCompleted()) {
                $taskGuids[] = $task->guid()->toString();
            }
        }

        $this->connection->delete('tasks', [['guid' => $taskGuids]]);
    }
}
