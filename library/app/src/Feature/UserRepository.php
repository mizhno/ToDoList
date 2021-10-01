<?php

declare(strict_types=1);

namespace Library\Feature;

use Library\Infrastructure\Database\Connection;

final class UserRepository
{
    public function __construct(private Connection $connection)
    {
    }

    public function add(User $user): User
    {
        $this->connection->insert(
            'users',
            ['guid', 'login', 'password'],
            [[
                $user->toArrayForAdd(),
            ]]
        );

        return $user;
    }

    /** @todo удалить */
    public function findByListGuid(string $guid): ?User
    {
        $userGuid = $this->connection->selectOneRow('users_tasklists', ['users'], [['tasklists' => $guid]]);

        return $this->findByGuid($userGuid['users']);
    }

    public function findByTaskList(TaskList $taskList): ?User
    {
        $userGuid = $this->connection->selectOneRow('users_tasklists', ['users'], [['tasklists' => $taskList->guid()->toString()]]);

        return $this->findByGuid($userGuid['users']);
    }

    public function findByGuid(string $guid): ?User
    {
        $user = $this->connection->selectOneRow('users', ['*'], [['guid' => $guid]]);

        if (empty($user)) {
            return null;
        }

        return User::fromArray($user);
    }

    public function findByToken(string $token): ?User
    {
        $user = $this->connection->selectOneRow(
            'users',
            ['*'],
            [['token' => $token]]
        );

        if (empty($user)) {
            return null;
        }

        return User::fromArray($user);
    }

    public function findByLogin(string $login): ?User
    {
        $user = $this->connection->selectOneRow(
            'users',
            ['*'],
            [['login' => $login]]
        );

        if (empty($user)) {
            return null;
        }

        return User::fromArray($user);
    }
}
