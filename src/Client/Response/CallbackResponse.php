<?php

declare(strict_types=1);

namespace Setono\Kraken\Client\Response;

use Webmozart\Assert\Assert;

final class CallbackResponse extends Response
{
    /** @var mixed */
    private $id;

    public function __construct(array $data)
    {
        parent::__construct($data);

        Assert::keyExists($data, 'id');

        $this->id = $data['id'];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}
