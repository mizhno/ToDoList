<?php

declare(strict_types=1);

namespace Library\Feature;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

final class User
{
    public function __construct(
        private UuidInterface $guid,
        private string $login,
        private string $password,
        private ?string $token = null,
    ) {
    }

    public function guid(): UuidInterface
    {
        return $this->guid;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function authenticate(string $token): self
    {
        $user = clone $this;
        $user->token = $token;

        return $user;
    }

    public function toArray(): array
    {
        return [
            'guid' => $this->guid()->toString(),
            'login' => $this->login(),
            'password' => $this->password(),
            'token' => $this->token,
        ];
    }

    public function toArrayForAdd(): array
    {
        return [
            'guid' => $this->guid()->toString(),
            'login' => $this->login(),
            'password' => $this->password(),
        ];
    }

    public static function create(string $login, string $password): self
    {
        Assert::notEmpty($login);
        Assert::notEmpty($password);

        return new self(Uuid::uuid1(), $login, $password);
    }

    public static function fromArray($data): self
    {
        Assert::notEmpty($data['guid']);
        Assert::notEmpty($data['login']);
        Assert::notEmpty($data['password']);

        return new self(
            guid: Uuid::fromString($data['guid']),
            login: $data['login'],
            password: $data['password'],
            token: $data['token'] ?? null,
        );
    }

    private function login(): string
    {
        return $this->login;
    }
}
