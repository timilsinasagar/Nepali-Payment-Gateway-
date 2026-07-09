<?php

namespace Sagartimilsina\NepalPayment\DataTransferObjects;

use Sagartimilsina\NepalPayment\Enums\Gateway;

/**
 * Result of starting a payment. Khalti and eSewa work differently at the
 * HTTP level, so this DTO supports both shapes:
 *
 *  - Khalti calls an API up front and gives you a ready-made URL to
 *    redirect the browser to directly. `redirectUrl` will be set,
 *    `formFields`/`formAction` will be null.
 *
 *  - eSewa v2 does NOT hand you a URL. Instead you must POST a signed
 *    HTML form to their endpoint (the browser needs to submit it,
 *    typically via an auto-submitting form or JS). `formAction` and
 *    `formFields` will be set, `redirectUrl` will be null.
 *
 * Check `requiresFormPost()` to know which case you're in.
 */
final class PaymentInitiationResult
{
    /**
     * @param  array<string, string>|null  $formFields
     */
    public function __construct(
        public readonly Gateway $gateway,
        public readonly string $gatewayReference,
        public readonly ?string $redirectUrl = null,
        public readonly ?string $formAction = null,
        public readonly ?array $formFields = null,
        public readonly array $raw = [],
    ) {
    }

    public function requiresFormPost(): bool
    {
        return $this->formAction !== null && $this->formFields !== null;
    }
}
