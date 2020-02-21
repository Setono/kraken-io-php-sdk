<?php

declare(strict_types=1);

namespace Setono\Kraken\Client;

use Buzz\Client\Curl;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface as HttpRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface as HttpStreamFactoryInterface;
use RuntimeException;
use function Safe\json_encode;
use Setono\Kraken\Client\Response\Response;
use Setono\Kraken\Client\Response\ResponseFactory;
use Setono\Kraken\Client\Response\ResponseFactoryInterface;
use Setono\Kraken\Exception\RequestFailedException;

final class Client implements ClientInterface
{
    /**
     * If true the dev flag will be set to true on every request to Kraken
     *
     * @var bool
     */
    private $dev = false;

    /** @var Psr17Factory */
    private $psr17Factory;

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var HttpRequestFactoryInterface */
    private $httpRequestFactory;

    /** @var HttpStreamFactoryInterface */
    private $httpStreamFactory;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /** @var array */
    private $auth;

    /** @var string */
    private $apiKey;

    /** @var string */
    private $apiSecret;

    /** @var string */
    private $baseUrl;

    public function __construct(
        string $apiKey,
        string $apiSecret,
        HttpClientInterface $httpClient = null,
        HttpRequestFactoryInterface $httpRequestFactory = null,
        HttpStreamFactoryInterface $httpStreamFactory = null,
        ResponseFactoryInterface $responseFactory = null,
        string $baseUrl = 'https://api.kraken.io'
    ) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;

        if (null === $httpClient) {
            if (!class_exists(Curl::class)) {
                throw new RuntimeException('No HTTP client given and the Buzz library was not found. Fix this by running: composer require kriswallsmith/buzz');
            }

            $httpClient = new Curl($this->getPsr17Factory('No HTTP client given, but Buzz library is found. However, that library needs a PSR17 factory.'));
        }

        if (null === $httpRequestFactory) {
            $httpRequestFactory = $this->getPsr17Factory('No HTTP request factory given and the Psr7 library was not found.');
        }

        if (null === $httpStreamFactory) {
            $httpStreamFactory = $this->getPsr17Factory('No HTTP stream factory given and the Psr7 library was not found.');
        }

        if (null === $responseFactory) {
            $responseFactory = new ResponseFactory();
        }

        $this->httpClient = $httpClient;
        $this->httpRequestFactory = $httpRequestFactory;
        $this->httpStreamFactory = $httpStreamFactory;
        $this->responseFactory = $responseFactory;
        $this->baseUrl = $baseUrl;
    }

    public function url(string $url, bool $lossy, bool $wait = true, array $extra = []): Response
    {
        return $this->sendRequest('v1/url', array_merge([
            'url' => $url,
            'wait' => $wait,
            'lossy' => $lossy,
        ], $extra));
    }

    public function status(): Response
    {
        return $this->sendRequest('user_status');
    }

    public function setDev(bool $dev = true): void
    {
        $this->dev = $dev;
    }

    private function sendRequest(string $endpoint, array $body = []): Response
    {
        $body = array_merge([
            'auth' => [
                'api_key' => $this->apiKey,
                'api_secret' => $this->apiSecret,
            ],
        ], $body);

        if ($this->dev) {
            $body['dev'] = true;
        }

        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');

        $request = $this->httpRequestFactory
            ->createRequest('POST', $url)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($this->httpStreamFactory->createStream(json_encode($body)))
        ;

        $response = $this->httpClient->sendRequest($request);

        if ($response->getStatusCode() !== 200) {
            throw new RequestFailedException($request, $response);
        }

        return $this->responseFactory->createResponse($response);
    }

    private function getPsr17Factory(string $exceptionMessage): Psr17Factory
    {
        if (null === $this->psr17Factory) {
            if (!class_exists(Psr17Factory::class)) {
                throw new RuntimeException($exceptionMessage . ' Fix this by running: composer require nyholm/psr7');
            }

            $this->psr17Factory = new Psr17Factory();
        }

        return $this->psr17Factory;
    }
}
