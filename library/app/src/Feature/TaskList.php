<?php

declare(strict_types=1);

namespace Library\Feature;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

final class TaskList
{
    private ?string $title = null;

    private function __construct(
        private UuidInterface $guid,
        private ?User $user,
        private ?array $tasks = [],
    ) {
    }

    public function guid(): UuidInterface
    {
        return $this->guid;
    }

    public static function create(string $title, User $user): self
    {
        Assert::notEmpty($title);

        $taskList = new self(Uuid::uuid1(), $user);
        $taskList->title = $title;

        return $taskList;
    }

    public static function createDefault(User $user): self
    {
        return new self(Uuid::uuid1(), $user);
    }

    public static function createFromArray(array $data, User $user, array $tasks = []): self
    {
        Assert::notEmpty($data['guid']);
        Assert::notEmpty($data['title']);

        $taskList = new self(Uuid::fromString($data['guid']), $user);
        $taskList->title = $data['title'];
        $taskList->tasks = $tasks;

        return $taskList;
    }

    /**
     * @return null|Task[]
     */
    public function tasks(): ?array
    {
        // сделать также как с user
        return $this->tasks;
    }

    public function toArray(): array
    {
        return [
            'guid' => $this->guid()->toString(),
            'user' => $this->user()->toArray(),
            'title' => $this->title(),
            'tasks' => array_map(
                static fn(Task $task): array => $task->toArrayForAdd(),
                $this->tasks(),
            ),
        ];
    }

    public function user(): User
    {
        // if (! isset($this->user))
        // userRepository->getUserByTaskList($this)
        return $this->user;
    }

    public function title(): ?string
    {
        return $this->title;
    }
}
