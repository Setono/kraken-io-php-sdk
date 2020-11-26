<?php

declare(strict_types=1);

namespace Setono\Kraken\Exception;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;

final class RequestFailedExceptionTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_correct_values(): void
    {
        $psr17Factory = new Psr17Factory();
        $request = $psr17Factory->createRequest('GET', 'https://example.com');
        $request = $request->withBody($psr17Factory->createStream(json_encode([
            'auth' => [
                'api_key' => 'secret_key',
                'api_secret' => 'secret',
            ],
        ])));

        $response = $psr17Factory->createResponse();
        $response = $response->withBody($psr17Factory->createStream(json_encode([
            'error' => 'Error message',
        ])));

        $exception = new RequestFailedException($request, $response);

        self::assertSame($request, $exception->getRequest());
        self::assertSame($response, $exception->getResponse());
        self::assertSame(<<<EXPECTED
Request failed with message: Error message. HTTP status code was: 200. Request body was:
Array
(
    [auth] => Array
        (
            [api_key] => ********
            [api_secret] => ********
        )

)

EXPECTED,
        $exception->getMessage());
    }
}
