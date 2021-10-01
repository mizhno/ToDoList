<?php

declare(strict_types=1);

namespace Library\Feature;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

final class Task
{
    public const FINISHED = 'finished';
    public const UNFINISHED = 'unfinished';

    private string $title;
    private string $status;
    private DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $completedAt = null;

    private function __construct(private UuidInterface $guid)
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function guid(): UuidInterface
    {
        return $this->guid;
    }

    public static function create(string $title): self
    {
        Assert::notEmpty($title);

        $task = new self(Uuid::uuid1());

        $task->title = $title;
        $task->status = self::UNFINISHED;

        return $task;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::FINISHED;
    }

    public function renameTo(string $title): self
    {
        Assert::notEmpty($title);

        $task = clone $this;
        $task->title = $title;

        return $task;
    }

    public function toggle(): self
    {
        $task = clone $this;

        if ($task->status === self::FINISHED) {
            $task->status = self::UNFINISHED;
            $task->completedAt = null;

            return $task;
        }

        $task->status = self::FINISHED;
        $task->completedAt = new DateTimeImmutable('c');

        return $task;
    }

    public function toArrayForAdd(): array
    {
        return [
            'guid' => $this->guid()->toString(),
            'title' => $this->title(),
            'status' => $this->status,
            'create_time' => $this->createdAt()->format('c'),
        ];
    }

    public function toArrayForSave(): array
    {
        return [
            'guid' => $this->guid()->toString(),
            'title' => $this->title(),
            'status' => $this->status,
            'complete_time' => empty($this->completedAt()) ? null : $this->completedAt()->format('c'),
        ];
    }

    public function toArray(): array
    {
        return [
            'guid' => $this->guid()->toString(),
            'title' => $this->title(),
            'status' => $this->status,
            'create_time' => $this->createdAt(),
            'complete_time' => $this->completedAt(),
        ];
    }

    public static function createFromArray(array $data): self
    {
        Assert::notEmpty($data['guid']);
        Assert::notEmpty($data['title']);
        Assert::notEmpty($data['status']);
        Assert::notEmpty($data['create_time']);

        $task = new self(Uuid::fromString($data['guid']));

        $task->title = $data['title'];
        $task->status = $data['status'];
        $task->createdAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['create_time']);
        $task->completedAt = isset($data['update_time']) ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['update_time']) : null;

        return $task;
    }

    private function title(): string
    {
        return $this->title;
    }

    private function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    private function completedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }
}
