<?php

declare(strict_types=1);

namespace Library\Feature\User\Register;

use Library\Feature\User;
use Library\Feature\UserRepository;

final class UserRegistrationService
{
    public function __construct(private UserRepository $repository)
    {
    }

    public function register(array $data): ?User
    {
        $user = User::create(
            $data['login'],
            password_hash($data['password'], PASSWORD_DEFAULT),
        );

        $exist = $this->repository->findByLogin($data['login']);

        if (!empty($exist)) {
            return null;
        }

        $this->repository->add($user);

        return $user;
    }
}
