<?php

declare(strict_types=1);

namespace Setono\Kraken\Exception;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class RequestFailedExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_correct_values(): void
    {
        $psr17Factory = new Psr17Factory();
        $request = $psr17Factory->createRequest('GET', 'https://example.com');
        $response = $psr17Factory->createResponse();
        $response = $response->withBody($psr17Factory->createStream(json_encode([
            'error' => 'Error message'
        ])));

        $exception = new RequestFailedException($request, $response);

        $this->assertSame($request, $exception->getRequest());
        $this->assertSame($response, $exception->getResponse());
        $this->assertSame('Request failed with message: Error message. HTTP status code was: 200', $exception->getMessage());
    }
}
