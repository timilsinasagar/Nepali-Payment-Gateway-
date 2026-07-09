<?php

namespace Sagartimilsina\NepalPayment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the bundled demo's "start a payment" form. Kept separate
 * from the controller so validation always runs before any gateway
 * code executes, same pattern as the nepali-date package.
 */
class StartPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gateway' => ['required', 'string', 'in:esewa,khalti'],
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'order_name' => ['required', 'string', 'max:120'],
        ];
    }

    public function gateway(): string
    {
        return (string) $this->validated('gateway');
    }

    public function amount(): float
    {
        return (float) $this->validated('amount');
    }

    public function orderName(): string
    {
        return (string) $this->validated('order_name');
    }
}
