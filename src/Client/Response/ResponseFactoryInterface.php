<?php

declare(strict_types=1);

namespace Setono\Kraken\Client\Response;

use Psr\Http\Message\ResponseInterface;

interface ResponseFactoryInterface
{
    public function createResponse(ResponseInterface $response): Response;
}
