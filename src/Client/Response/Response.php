<?php

declare(strict_types=1);

namespace Setono\Kraken\Client\Response;

use LogicException;
use Webmozart\Assert\Assert;

class Response
{
    /** @var array */
    protected $data;

    public function __construct(array $data)
    {
        Assert::keyExists($data, 'success');
        $this->data = $data;

        if (!isset($data['success']) || $data['success'] !== true) {
            throw new LogicException('A response should not be possible to be created with an unsuccessful request');
        }
    }

    public function getData(): array
    {
        return $this->data;
    }
}
