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
use Sagartimilsina\NepalPayment\Support\EsewaSigner;
use Sagartimilsina\NepalPayment\Support\HttpClient;

/**
 * eSewa v2 / ePay integration.
 *
 * Unlike Khalti, eSewa does not give you a redirect URL from an API
 * call. Instead you build a signed HTML form and the *browser* POSTs it
 * directly to eSewa. initiate() returns the form action URL and fields;
 * your view is responsible for rendering (and usually auto-submitting)
 * that form. See resources/views/demo.blade.php for a working example.
 *
 * Docs: https://developer.esewa.com.np/pages/Epay
 */
final class EsewaGateway implements PaymentGatewayInterface
{
    private readonly EsewaSigner $signer;

    public function __construct(
        private readonly string $productCode,
        private readonly string $secretKey,
        private readonly string $formAction,
        private readonly string $statusCheckUrl,
        private readonly HttpClient $httpClient = new HttpClient(),
    ) {
        if ($this->productCode === '' || $this->secretKey === '') {
            throw new InvalidConfigurationException(
                'eSewa product_code and secret_key must both be configured before initiating a payment.'
            );
        }

        $this->signer = new EsewaSigner($this->secretKey);
    }

    public function initiate(PaymentInitiationRequest $request): PaymentInitiationResult
    {
        $totalAmount = $this->formatAmount($request->totalAmount());

        // eSewa signs exactly these three fields, in exactly this order.
        // Changing the order changes the signature -- do not "clean up"
        // this array into alphabetical order or similar.
        $signedFields = [
            'total_amount' => $totalAmount,
            'transaction_uuid' => $request->orderId,
            'product_code' => $this->productCode,
        ];

        $signature = $this->signer->sign($signedFields);

        $formFields = [
            'amount' => $this->formatAmount($request->amount),
            'tax_amount' => $this->formatAmount($request->taxAmount),
            'total_amount' => $totalAmount,
            'transaction_uuid' => $request->orderId,
            'product_code' => $this->productCode,
            'product_service_charge' => $this->formatAmount($request->productServiceCharge),
            'product_delivery_charge' => $this->formatAmount($request->productDeliveryCharge),
            'success_url' => $request->successUrl,
            'failure_url' => $request->failureUrl,
            'signed_field_names' => implode(',', array_keys($signedFields)),
            'signature' => $signature,
        ];

        return new PaymentInitiationResult(
            gateway: Gateway::ESEWA,
            gatewayReference: $request->orderId,
            formAction: $this->formAction,
            formFields: $formFields,
            raw: $formFields,
        );
    }

    /**
     * Calls eSewa's status-check endpoint and verifies the response
     * before trusting anything in it.
     *
     * @param  string  $reference  The transaction_uuid you generated when calling initiate().
     * @param  array{amount: float}  $context  Required. eSewa's status API needs the
     *   total amount as a query parameter -- there's no way to look it up from the
     *   reference alone, so you must pass it (e.g. from your own orders table).
     */
    public function verify(string $reference, array $context = []): PaymentVerificationResult
    {
        if (! isset($context['amount'])) {
            throw new InvalidConfigurationException(
                "eSewa verification requires the original total amount. Call verify(\$reference, ['amount' => \$totalAmount])."
            );
        }

        $totalAmount = (float) $context['amount'];
        $formattedAmount = $this->formatAmount($totalAmount);

        $query = http_build_query([
            'product_code' => $this->productCode,
            'total_amount' => $formattedAmount,
            'transaction_uuid' => $reference,
        ]);

        $response = $this->httpClient->get("{$this->statusCheckUrl}?{$query}");
        $body = $response['body'];

        $status = (string) ($body['status'] ?? 'NOT_FOUND');

        $knownStatuses = ['COMPLETE', 'PENDING', 'FULL_REFUND', 'PARTIAL_REFUND', 'AMBIGUOUS', 'NOT_FOUND', 'CANCELED'];

        if (! in_array($status, $knownStatuses, true)) {
            throw new VerificationFailedException(
                "eSewa returned an unrecognized status: {$status}"
            );
        }

        return new PaymentVerificationResult(
            gateway: Gateway::ESEWA,
            status: $this->mapStatus($status),
            gatewayReference: $reference,
            amount: $totalAmount,
            raw: $body,
        );
    }

    private function mapStatus(string $esewaStatus): PaymentStatus
    {
        return match ($esewaStatus) {
            'COMPLETE' => PaymentStatus::SUCCESS,
            'PENDING', 'AMBIGUOUS' => PaymentStatus::PENDING,
            'FULL_REFUND', 'PARTIAL_REFUND' => PaymentStatus::REFUNDED,
            'CANCELED' => PaymentStatus::CANCELED,
            default => PaymentStatus::FAILED,
        };
    }

    /**
     * eSewa expects plain decimal strings like "1500.00", not "1,500.00"
     * or scientific notation.
     */
    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}