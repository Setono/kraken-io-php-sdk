<?php

declare(strict_types=1);

namespace Setono\Kraken\Client;

use Setono\Kraken\Client\Response\Response;

interface ClientInterface
{
    /**
     * Corresponds to this https://kraken.io/docs/upload-url#image-url
     *
     * @param array $extra Used to add extra params, i.e. params for Amazon s3 (https://kraken.io/docs/storage-s3)
     */
    public function url(string $url, bool $lossy, bool $wait = true, array $extra = []): Response;

    /**
     * Corresponds to this https://kraken.io/docs/user-status
     */
    public function status(): Response;
}
