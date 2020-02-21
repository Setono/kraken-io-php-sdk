<?php

declare(strict_types=1);

namespace Setono\Kraken\Client;

use PHPUnit\Framework\TestCase;
use Setono\Kraken\Client\Response\UserStatusResponse;
use Setono\Kraken\Client\Response\WaitResponse;

final class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function upload_with_url(): void
    {
        $client = self::getClient();

        $response = $client->url('http://www.example.com', true);

        $this->assertInstanceOf(WaitResponse::class, $response);
    }

    /**
     * @test
     */
    public function get_user_status(): void
    {
        $client = self::getClient();

        $response = $client->status();

        $this->assertInstanceOf(UserStatusResponse::class, $response);
    }

    private static function getClient(): Client
    {
        $apiKey = getenv('KRAKEN_API_KEY');
        if(false === $apiKey) {
            $apiKey = 'api key';
        }

        $apiSecret = getenv('KRAKEN_API_SECRET');
        if(false === $apiSecret) {
            $apiSecret = 'api key';
        }

        $client = new Client($apiKey, $apiSecret);
        $client->setDev();

        return $client;
    }
}
