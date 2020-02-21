<?php

declare(strict_types=1);

namespace Setono\Kraken\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
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

        parent::__construct(sprintf(
            'Request failed with message: %s. HTTP status code was: %s', $message, $response->getStatusCode()
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
}
