<?php

declare(strict_types=1);

namespace Library\Feature\User\Authenticate\Api;

use Library\Feature\User\Authenticate\UserAuthenticationService;
use Library\Infrastructure\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UserAuthenticationHandler
{
    public function __construct(private UserAuthenticationService $authenticationService)
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

        $user = $this->authenticationService->authenticate($inputData);

        if (empty($user)) {
            return Response::create(404);
        }

        return Response::create(201, $user->toArray());
    }
}
