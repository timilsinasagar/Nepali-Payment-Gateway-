<?php

namespace Sagartimilsina\NepalPayment\Contracts;

use Sagartimilsina\NepalPayment\DataTransferObjects\PaymentInitiationRequest;
use Sagartimilsina\NepalPayment\DataTransferObjects\PaymentInitiationResult;
use Sagartimilsina\NepalPayment\DataTransferObjects\PaymentVerificationResult;

interface PaymentGatewayInterface
{
    /**
     * Start a payment. Does not move any money yet -- it either builds a
     * redirect URL or a signed form, depending on the gateway.
     */
    public function initiate(PaymentInitiationRequest $request): PaymentInitiationResult;

    /**
     * Ask the gateway's server directly whether a payment succeeded.
     * `reference` is the gateway's own transaction identifier
     * (transaction_uuid for eSewa, pidx for Khalti) -- NOT your order ID.
     *
     * Always call this server-side after the user is redirected back.
     * Never trust the success/failure redirect alone, since query
     * parameters can be edited by the user in the browser.
     *
     * @param  array<string, mixed>  $context  Gateway-specific extras. eSewa
     *   requires ['amount' => float] here since its status API needs the
     *   total amount as a query parameter. Khalti ignores $context entirely.
     */
    public function verify(string $reference, array $context = []): PaymentVerificationResult;
}
