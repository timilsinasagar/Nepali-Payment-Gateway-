<?php

namespace Sagartimilsina\NepalPayment\Gateways;

use Sagartimilsina\NepalPayment\Contracts\PaymentGatewayInterface;
use Sagartimilsina\NepalPayment\DataTransferObjects\PaymentInitiationRequest;
use Sagartimilsina\NepalPayment\DataTransferObjects\PaymentInitiationResult;
use Sagartimilsina\NepalPayment\DataTransferObjects\PaymentVerificationResult;
use Sagartimilsina\NepalPayment\Enums\Gateway;
use Sagartimilsina\NepalPayment\Enums\PaymentStatus;
use Sagartimilsina\NepalPayment\Exceptions\InvalidConfigurationException;
use Sagartimilsina\NepalPayment\Exceptions\VerificationFailedException;
use Sagartimilsina\NepalPayment\Support\HttpClient;

/**
 * Khalti ePayment API v2 integration.
 *
 * Unlike eSewa, Khalti's initiate() call hits their API immediately and
 * gives back a ready-to-use `payment_url` -- you just redirect the
 * browser there, no form-building needed.
 *
 * Docs: https://docs.khalti.com/khalti-epayment/
 */
final class KhaltiGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $baseUrl,
        private readonly HttpClient $httpClient = new HttpClient(),
    ) {
        if ($this->secretKey === '') {
            throw new InvalidConfigurationException(
                'Khalti secret_key must be configured (set KHALTI_SECRET_KEY in your .env) before initiating a payment.'
            );
        }
    }

    public function initiate(PaymentInitiationRequest $request): PaymentInitiationResult
    {
        // Khalti's API takes amount in *paisa* (1 NPR = 100 paisa), not
        // rupees. This is the single most common mistake when
        // integrating Khalti -- forgetting to multiply by 100 either
        // overcharges the customer by 100x or fails validation outright.
        $amountInPaisa = (int) round($request->totalAmount() * 100);

        $payload = [
            'return_url' => $request->successUrl,
            'website_url' => $this->extractOrigin($request->successUrl),
            'amount' => $amountInPaisa,
            'purchase_order_id' => $request->orderId,
            'purchase_order_name' => $request->orderName,
        ];

        if ($request->customerInfo !== []) {
            $payload['customer_info'] = $request->customerInfo;
        }

        $response = $this->httpClient->post(
            "{$this->baseUrl}/epayment/initiate/",
            $payload,
            ['Authorization' => "Key {$this->secretKey}"],
        );

        $body = $response['body'];

        if ($response['status'] !== 200 || ! isset($body['pidx'], $body['payment_url'])) {
            $detail = json_encode($body);

            throw new VerificationFailedException(
                "Khalti payment initiation failed (HTTP {$response['status']}): {$detail}"
            );
        }

        return new PaymentInitiationResult(
            gateway: Gateway::KHALTI,
            gatewayReference: $body['pidx'],
            redirectUrl: $body['payment_url'],
            raw: $body,
        );
    }

    /**
     * @param  string  $reference  The `pidx` returned by initiate(). $context is unused for Khalti.
     */
    public function verify(string $reference, array $context = []): PaymentVerificationResult
    {
        $response = $this->httpClient->post(
            "{$this->baseUrl}/epayment/lookup/",
            ['pidx' => $reference],
            ['Authorization' => "Key {$this->secretKey}"],
        );

        $body = $response['body'];

        if ($response['status'] !== 200 || ! isset($body['status'])) {
            $detail = json_encode($body);

            throw new VerificationFailedException(
                "Khalti payment lookup failed (HTTP {$response['status']}): {$detail}"
            );
        }

        // Khalti returns the amount in paisa too -- convert back to NPR
        // to keep the result consistent with what your app passed in.
        $amountInNpr = isset($body['total_amount']) ? ((float) $body['total_amount']) / 100 : 0.0;

        return new PaymentVerificationResult(
            gateway: Gateway::KHALTI,
            status: $this->mapStatus((string) $body['status']),
            gatewayReference: $reference,
            amount: $amountInNpr,
            raw: $body,
        );
    }

    private function mapStatus(string $khaltiStatus): PaymentStatus
    {
        return match ($khaltiStatus) {
            'Completed' => PaymentStatus::SUCCESS,
            'Pending' => PaymentStatus::PENDING,
            'Refunded', 'Partially Refunded' => PaymentStatus::REFUNDED,
            'User canceled' => PaymentStatus::CANCELED,
            default => PaymentStatus::FAILED, // covers "Expired", "Not Found", etc.
        };
    }

    private function extractOrigin(string $url): string
    {
        $parts = parse_url($url);

        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            throw new InvalidConfigurationException(
                "Could not determine website_url from success_url: {$url}. Pass a full URL like https://example.com/payment/success."
            );
        }

        $origin = "{$parts['scheme']}://{$parts['host']}";

        if (isset($parts['port'])) {
            $origin .= ":{$parts['port']}";
        }

        return $origin;
    }
}