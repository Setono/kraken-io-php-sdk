<?php

declare(strict_types=1);

namespace spec\Setono\Kraken\Client;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Client\ClientInterface as HttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Setono\Kraken\Client\Client;
use Setono\Kraken\Client\ClientInterface;

class ClientSpec extends ObjectBehavior
{
    private const API_KEY = 'api_key';

    private const BASE_URL = 'https://api2.postnord.com';

    public function let(
        HttpClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ): void {
        $this->beConstructedWith($httpClient, $requestFactory, $streamFactory, self::API_KEY);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(Client::class);
    }

    public function it_implements_client_interface(): void
    {
        $this->shouldImplement(ClientInterface::class);
    }

    public function it_gets(
        HttpClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        ResponseInterface $response,
        StreamInterface $stream
    ): void {
        $requestFactory
            ->createRequest('GET', self::BASE_URL . '/endpoint.json?apikey=' . self::API_KEY . '&param1=value1&param2=value2')
            ->shouldBeCalled();

        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn($stream);
        $stream->__toString()->willReturn('{"items":[1,2,3]}');
        $httpClient->sendRequest(Argument::any())->willReturn($response);

        $this->get('endpoint.json', [
            'param1' => 'value1',
            'param2' => 'value2',
        ])->shouldReturn([
            'items' => [1, 2, 3],
        ]);
    }

    public function it_posts(
        HttpClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        RequestInterface $request,
        ResponseInterface $response,
        StreamInterface $stream
    ): void {
        $request->withBody(Argument::any())->willReturn($request);

        $requestFactory
            ->createRequest('POST', self::BASE_URL . '/endpoint.json?apikey=' . self::API_KEY . '&param1=value1&param2=value2')
            ->willReturn($request);

        $streamFactory
            ->createStream('{"post_param1":"post_value1","post_param2":"post_value2"}')
            ->shouldBeCalled();

        $response->getStatusCode()->willReturn(200);
        $response->getBody()->willReturn($stream);
        $stream->__toString()->willReturn('{"items":[1,2,3]}');
        $httpClient->sendRequest($request)->willReturn($response);

        $this->post('endpoint.json', [
            'param1' => 'value1',
            'param2' => 'value2',
        ], [
            'post_param1' => 'post_value1',
            'post_param2' => 'post_value2',
        ])->shouldReturn([
            'items' => [1, 2, 3],
        ]);
    }
}
