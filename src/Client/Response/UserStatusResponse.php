<?php

declare(strict_types=1);

namespace Setono\Kraken\Client\Response;

use Webmozart\Assert\Assert;

final class UserStatusResponse extends Response
{
    /** @var bool */
    private $active;

    /** @var string */
    private $planName;

    /** @var int */
    private $quotaTotal;

    /** @var int */
    private $quotaUsed;

    /** @var int */
    private $quotaRemaining;

    public function __construct(array $data)
    {
        parent::__construct($data);

        Assert::keyExists($data, 'active');
        Assert::keyExists($data, 'plan_name');
        Assert::keyExists($data, 'quota_total');
        Assert::keyExists($data, 'quota_used');
        Assert::keyExists($data, 'quota_remaining');

        $this->active = $data['active'];
        $this->planName = $data['plan_name'];
        $this->quotaTotal = $data['quota_total'];
        $this->quotaUsed = $data['quota_used'];
        $this->quotaRemaining = $data['quota_remaining'];
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getPlanName(): string
    {
        return $this->planName;
    }

    public function getQuotaTotal(): int
    {
        return $this->quotaTotal;
    }

    public function getQuotaUsed(): int
    {
        return $this->quotaUsed;
    }

    public function getQuotaRemaining(): int
    {
        return $this->quotaRemaining;
    }
}
