<?php

declare(strict_types=1);

namespace Library\Feature\User\Authenticate\Api;

use Library\Feature\UserRepository;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AutheticationMiddleware
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('Token');

        if (empty($token)) {
            return new Response(401);
        }

        $user = $this->userRepository->findByToken($token);

        if (empty($user)) {
            return new Response(401);
        }

        $request = $request->withAttribute('User', $user);

        return $handler->handle($request);
    }
}
