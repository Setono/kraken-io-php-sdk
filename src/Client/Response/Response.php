<?php

declare(strict_types=1);

namespace Setono\Kraken\Client\Response;

use LogicException;

class Response
{
    /** @var array */
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;

        if (isset($data['success']) && $data['success'] !== true) {
            throw new LogicException('The response was not successful');
        }
    }

    public function getData(): array
    {
        return $this->data;
    }
}
