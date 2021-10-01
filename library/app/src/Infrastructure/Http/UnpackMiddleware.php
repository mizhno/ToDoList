<?php

declare(strict_types=1);

namespace Library\Infrastructure\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class UnpackMiddleware
{
    public function __construct()
    {
    }

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $content = $request->getHeader('Content-Type');
        $data = $request->getBody()->getContents();

        if (array_shift($content) === 'application/json') {
            $data = json_decode($data, true, JSON_THROW_ON_ERROR);
        }

        $request = $request->withAttribute('Data', $data);

        return $handler->handle($request);
    }
}
