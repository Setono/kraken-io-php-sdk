<?php

declare(strict_types=1);

namespace Setono\Kraken\Client\Response;

use Psr\Http\Message\ResponseInterface;
use function Safe\json_decode;

final class ResponseFactory implements ResponseFactoryInterface
{
    public function createResponse(ResponseInterface $response): Response
    {
        $data = (array) json_decode((string) $response->getBody(), true);

        if (isset($data['id'])) {
            return new CallbackResponse($data);
        }

        if (isset($data['kraked_url'])) {
            return new WaitResponse($data);
        }

        if (isset($data['plan_name'])) {
            return new UserStatusResponse($data);
        }

        return new Response($data);
    }
}
