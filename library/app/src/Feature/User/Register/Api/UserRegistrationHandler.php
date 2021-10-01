<?php

declare(strict_types=1);

namespace Library\Feature\User\Register\Api;

use Library\Feature\User\Register\UserRegistrationService;
use Library\Infrastructure\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserRegistrationHandler
{
    public function __construct(private UserRegistrationService $registrationService)
    {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $inputData = $request->getAttribute('Data');

        if (empty($inputData['login'])) {
            return Response::create(404);
        }

        if (empty($inputData['password'])) {
            return Response::create(404);
        }

        $user = $this->registrationService->register($inputData);

        if (empty($user)) {
            return Response::create(405);
        }

        return Response::create(201, $user->toArray());
    }
}
