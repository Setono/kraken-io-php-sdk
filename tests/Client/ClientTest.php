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
    /** @var string */
    private $apiKey = 'api key';

    /** @var string */
    private $apiSecret = 'api secret';

    /** @var bool */
    private $live = false;

    /** @var array */
    private $matchers = [];

    public function setUp(): void
    {
        $apiKey = getenv('KRAKEN_API_KEY');
        $apiSecret = getenv('KRAKEN_API_SECRET');

        if (false !== $apiKey && false !== $apiSecret) {
            $this->apiKey = $apiKey;
            $this->apiSecret = $apiSecret;
            $this->live = true;
        }

        $this->addMatcher(static function (string $uri, array $body) {
            return isset($body['wait']) && $body['wait'] === true && preg_match(sprintf('#%s#', 'v1/url'), $uri) === 1;
        }, [
            'success' => true,
            'file_name' => 'header.jpg',
            'original_size' => 324520,
            'kraked_size' => 165358,
            'saved_bytes' => 159162,
            'kraked_url' => 'http://dl.kraken.io/d1/aa/cd/2a2280c2ffc7b4906a09f78f46/header.jpg',
        ]);

        $this->addMatcher(static function (string $uri, array $body) {
            return preg_match(sprintf('#%s#', 'v1/url'), $uri) === 1 && isset($body['wait']) && $body['wait'] === false;
        }, [
            'id' => '456234891891',
        ]);

        $this->addMatcher(static function (string $uri) {
            return preg_match(sprintf('#%s#', 'user_status'), $uri) === 1;
        }, [
            'success' => true,
            'active' => true,
            'plan_name' => 'Enterprise',
            'quota_total' => 64424509440,
            'quota_used' => 313271610,
            'quota_remaining' => 64111237830,
        ]);
    }

    /**
     * @test
     */
    public function upload_with_url_and_wait(): void
    {
        $client = $this->getClient();

        $response = $client->url('https://via.placeholder.com/300/FFFFFF/808080?text=kraken.io', true);

        $this->assertInstanceOf(WaitResponse::class, $response);
    }

    /**
     * @test
     */
    public function upload_with_url_and_get_callback(): void
    {
        $client = $this->getClient();

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
        $response = $this->getClient()->status();
        $this->assertInstanceOf(UserStatusResponse::class, $response);
    }

    private function getClient(HttpClientInterface $httpClient = null): Client
    {
        $httpClient = null;
        if (!$this->live) {
            $httpClient = $this->getHttpClient();
        }

        return new Client($this->apiKey, $this->apiSecret, $httpClient);
    }

    private function getHttpClient(): HttpClientInterface
    {
        $httpClient = new class() implements HttpClientInterface {
            /** @var array */
            private $matchers;

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $body = [];
                if ('' !== (string) $request->getBody()) {
                    $body = json_decode((string) $request->getBody(), true);
                }

                foreach ($this->matchers as $matcher) {
                    $res = call_user_func($matcher['matcher'], (string) $request->getUri(), $body);
                    if (true === $res) {
                        return new HttpResponse(200, [], json_encode($matcher['return']));
                    }
                }

                throw new \RuntimeException('Could not match the request');
            }

            public function setMatchers(array $matchers): void
            {
                $this->matchers = $matchers;
            }
        };

        $httpClient->setMatchers($this->matchers);

        return $httpClient;
    }

    private function addMatcher(callable $matcher, array $return): void
    {
        $this->matchers[] = [
            'matcher' => $matcher,
            'return' => $return,
        ];
    }
}
