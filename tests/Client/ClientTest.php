<?php

declare(strict_types=1);

namespace Setono\Kraken\Client;

use Nyholm\Psr7\Response as HttpResponse;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Setono\Kraken\Client\Response\CallbackResponse;
use Setono\Kraken\Client\Response\UserStatusResponse;
use Setono\Kraken\Client\Response\WaitResponse;

final class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function upload_with_url_and_wait(): void
    {
        $httpClient = $this->getHttpClient(static function (string $uri, array $body) {
            return isset($body['wait']) && $body['wait'] === true && preg_match(sprintf('#%s#', 'v1/url'), $uri) === 1;
        }, [
            'success' => true,
            'file_name' => 'header.jpg',
            'original_size' => 324520,
            'kraked_size' => 165358,
            'saved_bytes' => 159162,
            'kraked_url' => 'http://dl.kraken.io/d1/aa/cd/2a2280c2ffc7b4906a09f78f46/header.jpg',
        ]);
        $client = $this->getClient($httpClient);

        $response = $client->url('http://www.example.com', true);

        $this->assertInstanceOf(WaitResponse::class, $response);
    }

    /**
     * @test
     */
    public function upload_with_url_and_get_callback(): void
    {
        $httpClient = $this->getHttpClient(static function (string $uri, array $body) {
            return preg_match(sprintf('#%s#', 'v1/url'), $uri) === 1 && isset($body['wait']) && $body['wait'] === false;
        }, [
            'id' => '456234891891',
        ]);
        $client = $this->getClient($httpClient);

        $response = $client->url('http://www.example.com', true, false, [
            'callback_url' => 'https://example.com/callback',
        ]);

        $this->assertInstanceOf(CallbackResponse::class, $response);
    }

    /**
     * @test
     */
    public function get_user_status(): void
    {
        $httpClient = $this->getHttpClient(static function (string $uri, array $body) {
            return preg_match(sprintf('#%s#', 'user_status'), $uri) === 1;
        }, [
            'success' => true,
            'active' => true,
            'plan_name' => 'Enterprise',
            'quota_total' => 64424509440,
            'quota_used' => 313271610,
            'quota_remaining' => 64111237830,
        ]);
        $client = $this->getClient($httpClient);

        $response = $client->status();

        $this->assertInstanceOf(UserStatusResponse::class, $response);
    }

    private function getClient(HttpClientInterface $httpClient): Client
    {
        return new Client('api key', 'api secret', $httpClient);
    }

    private function getHttpClient(callable $matcher, array $return): HttpClientInterface
    {
        $httpClient = new class() implements HttpClientInterface {
            /** @var callable */
            private $matcher;

            /** @var array */
            private $return;

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $body = [];
                if ('' !== (string) $request->getBody()) {
                    $body = json_decode((string) $request->getBody(), true);
                }

                $res = call_user_func($this->matcher, (string) $request->getUri(), $body);
                if (true === $res) {
                    return new HttpResponse(200, [], json_encode($this->return));
                }

                throw new \RuntimeException('Could not match the request');
            }

            public function setMatcher(callable $matcher): void
            {
                $this->matcher = $matcher;
            }

            public function setReturn(array $return): void
            {
                $this->return = $return;
            }
        };

        $httpClient->setMatcher($matcher);
        $httpClient->setReturn($return);

        return $httpClient;
    }
}
