<?php

declare(strict_types=1);

namespace Library\Infrastructure\Http;

use Nyholm\Psr7\Response as ParentResponse;
use Nyholm\Psr7\Stream;

class Response extends ParentResponse
{
    public function __construct(
        private int $status,
        private array $headers,
        private $body = null,
    ) {
        parent::__construct($this->status, $this->headers, $this->body);
    }

    public static function create(int $status, array $data = []): self
    {
        return new self(
            status: $status,
            headers: ['Content-Type' => 'application/json'],
            body: Stream::create(json_encode(
                $data,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
            )),
        );
    }
}
