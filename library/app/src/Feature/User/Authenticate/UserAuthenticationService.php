<?php

declare(strict_types=1);

namespace Library\Feature\User\Authenticate;

use DomainException;
use Library\Feature\User;
use Library\Feature\User\TokenGenerationService;
use Library\Feature\UserRepository;

final class UserAuthenticationService
{
    public function __construct(private UserRepository $userRepository, private TokenGenerationService $tokenGenerationService)
    {
    }

    public function authenticate(array $inputData): DomainException|User
    {
        $user = $this->userRepository->findByLogin($inputData['login']);

        if (empty($user)) {
            return new DomainException('User not found', 404);
        }

        if (!password_verify($inputData['password'], $user->password())) {
            return new DomainException('Wrong password', 400);
        }

        $user = $user->authenticate($this->tokenGenerationService->generate());

        return $this->userRepository->save($user);
    }
}
