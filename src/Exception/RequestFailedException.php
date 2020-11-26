<?php

declare(strict_types=1);

namespace Setono\Kraken\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Safe\Exceptions\JsonException;
use function Safe\json_decode;
use function Safe\sprintf;

final class RequestFailedException extends RuntimeException
{
    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;

        $data = (array) json_decode((string) $response->getBody(), true);

        $message = 'Empty error message';
        if (isset($data['error'])) {
            $message = $data['error'];
        } elseif (isset($data['message'])) {
            $message = $data['message'];
        }

        if (is_array($message)) {
            $message = implode('', $message);
        }

        parent::__construct(sprintf(
            "Request failed with message: %s. HTTP status code was: %s. Request body was:\n%s",
            $message, $response->getStatusCode(), self::getSafeRequestBody($this->request)
        ));
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    private static function getSafeRequestBody(RequestInterface $request): string
    {
        $body = (string) $request->getBody();

        try {
            $data = json_decode($body, true);
            $data['auth']['api_key'] = '********';
            $data['auth']['api_secret'] = '********';

            return print_r($data, true);
        } catch (JsonException $e) {
            return 'Could not decode the request body. Body was: "' . $body . '"';
        }
    }
}
