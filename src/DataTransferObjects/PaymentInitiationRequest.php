<?php

namespace Sagartimilsina\NepalPayment\DataTransferObjects;

/**
 * Everything needed to start a payment, regardless of which gateway
 * ends up handling it.
 */
final class PaymentInitiationRequest
{
    /**
     * @param  float  $amount  The item/product amount, in NPR (e.g. 1500.00). Does NOT include tax or charges.
     * @param  string  $orderId  Your own unique order/invoice reference. Must be unique per payment attempt.
     * @param  string  $orderName  A short human-readable label for the order (shown on Khalti's payment page).
     * @param  string  $successUrl  Where the user is redirected after a successful payment.
     * @param  string  $failureUrl  Where the user is redirected after a failed/canceled payment.
     * @param  float  $taxAmount  Tax portion, in NPR. eSewa only — Khalti has no separate tax field. Default 0.
     * @param  float  $productServiceCharge  Extra service charge, in NPR. eSewa only. Default 0.
     * @param  float  $productDeliveryCharge  Delivery charge, in NPR. eSewa only. Default 0.
     * @param  array<string, mixed>  $customerInfo  Optional customer details (name, email, phone). Khalti uses this for pre-filling.
     * @param  array<string, mixed>  $metadata  Anything else you want passed through and returned unchanged in the result.
     */
    public function __construct(
        public readonly float $amount,
        public readonly string $orderId,
        public readonly string $orderName,
        public readonly string $successUrl,
        public readonly string $failureUrl,
        public readonly float $taxAmount = 0.0,
        public readonly float $productServiceCharge = 0.0,
        public readonly float $productDeliveryCharge = 0.0,
        public readonly array $customerInfo = [],
        public readonly array $metadata = [],
    ) {
    }

    /**
     * Total the customer actually pays: amount + tax + service charge + delivery charge.
     */
    public function totalAmount(): float
    {
        return round(
            $this->amount + $this->taxAmount + $this->productServiceCharge + $this->productDeliveryCharge,
            2
        );
    }
}
