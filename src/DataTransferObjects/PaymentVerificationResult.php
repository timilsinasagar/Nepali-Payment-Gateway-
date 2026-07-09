<?php

namespace Sagartimilsina\NepalPayment\DataTransferObjects;

use Sagartimilsina\NepalPayment\Enums\Gateway;
use Sagartimilsina\NepalPayment\Enums\PaymentStatus;

/**
 * The verified truth about a payment, straight from the gateway's server
 * (never trust redirect query params alone -- always verify server-side).
 */
final class PaymentVerificationResult
{
    /**
     * @param  array<string, mixed>  $raw  The untouched, decoded response from the gateway. Useful for logging/debugging.
     */
    public function __construct(
        public readonly Gateway $gateway,
        public readonly PaymentStatus $status,
        public readonly string $gatewayReference,
        public readonly float $amount,
        public readonly array $raw = [],
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::SUCCESS;
    }
}
