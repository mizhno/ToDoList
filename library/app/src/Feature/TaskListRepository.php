<?php

declare(strict_types=1);

namespace Library\Feature;

use Library\Infrastructure\Database\Connection;
use Throwable;

final class TaskListRepository
{
    public function __construct(private Connection $connection, private UserRepository $userRepository)
    {
    }

    public function add(TaskList $taskList): ?TaskList
    {
        $this->connection->beginTransaction();

        try {
            $taskListArray = $taskList->toArray();

            $this->connection->insert(
                'tasklists',
                ['guid', 'title'],
                [[$taskListArray['guid'], $taskListArray['title']]]
            );

            $this->connection->insert(
                'users_tasklists',
                ['users', 'tasklists'],
                [[$taskListArray['user']['guid'], $taskListArray['guid']]]
            );

            $this->connection->commit();

            return $taskList;
        } catch (Throwable) {
            $this->connection->rollback();

            return null;
        }
    }

    /** @todo пусть просто возвращает TaskList без юзера и тасков */
    public function findByGuid(string $guid): ?TaskList
    {
        $data = $this->connection->selectOneRow('tasklists', ['*'], [['guid' => $guid]]);

        if (empty($data)) {
            return null;
        }

        $user = $this->userRepository->findByListGuid($guid);
        $taskGuids = $this->findTasksByListGuid($guid);
        $tasks = $this->findByGuids($taskGuids);

        return TaskList::createFromArray($data, $user, empty($tasks) ? [] : $tasks);
    }

    public function isItYourList(User $user, TaskList $taskList): bool
    {
        $userArray = $user->toArray();
        $taskListArray = $taskList->toArray();

        $data = $this->connection->selectOneRow('users_tasklists', ['*'], [['users' => $userArray], ['tasklists' => $taskListArray['guid']]]);

        return !empty($data);
    }

    /**
     * @todo Сделать с помощью джойна
     */
    public function findDefault(User $user): ?TaskList
    {
        $userArray = $user->toArray();

        $tasklistRows = iterator_to_array($this->connection->selectAllRows('users_tasklists', ['*'], [['users' => $userArray['guid']]]));

        $defaultTasklistGuids = [];

        foreach ($tasklistRows as $tasklistRow) {
            $defaultTasklistGuids[] = $tasklistRow['tasklists'];
        }
        $defaultTasklists = iterator_to_array($this->connection->selectAllRows('tasklists', ['*'], [['title' => TaskList::DEFAULT]]));

        $defaultListsData = [];

        foreach ($defaultTasklists as $defaultTasklist) {
            foreach ($defaultTasklistGuids as $defaultTasklistGuid) {
                if ($defaultTasklistGuid === $defaultTasklist['guid']) {
                    $defaultListsData = $defaultTasklist;
                }
            }
        }

        return empty($defaultListsData) ? null : TaskList::createFromArray($defaultListsData, $user);
    }

    /**
     * @param array<array<string,scalar>> $taskGuids
     */
    private function findByGuids(?array $taskGuids): ?array
    {
        if (empty($taskGuids)) {
            return null;
        }

        $taskRows = iterator_to_array($this->connection->selectAllRows('tasks', ['*'], [['guid' => $taskGuids]]));

        $tasks = [];

        foreach ($taskRows as $row) {
            $tasks[$row['guid']] = Task::createFromArray($row);
        }

        return $tasks;
    }

    private function findTasksByListGuid(string $guid): ?array
    {
        $taskGuidRows = iterator_to_array($this->connection->selectAllRows('tasklists_tasks', ['task'], [['tasklist' => $guid]]));
        $taskGuids = [];

        if (empty($taskGuidRows)) {
            return null;
        }
        foreach ($taskGuidRows as $taskGuidRow) {
            $taskGuids[] = $taskGuidRow['task'];
        }

        return $taskGuids;
    }
}
